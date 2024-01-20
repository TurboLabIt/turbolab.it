<?php
namespace App\Service\Cms;

use App\Entity\BaseEntity;
use App\Entity\Cms\Image as ImageEntity;
use App\Exception\ImageLogicException;
use Doctrine\ORM\EntityManagerInterface;
use TurboLabIt\BaseCommand\Service\ProjectDir;
use Imagine\Imagick\Imagine;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;
use Imagine\Image\Point;


/**
 * @link https://github.com/TurboLabIt/TurboLab.it/blob/main/docs/images.md
 */
class Image extends BaseCmsService
{
    const UPLOADED_IMAGES_FOLDER_NAME   = parent::UPLOADED_ASSET_FOLDER_NAME . "/images";

    const WATERMARK_FILEPATH            = 'images/logo/turbolab.it.png';
    const WATERMARK_COVERAGE_PERCENT    = 40;

    const HOW_MANY_FILES_PER_FOLDER = 5000;

    const SIZE_MIN  = 'min';
    const SIZE_MED  = 'med';
    const SIZE_MAX  = 'max';

    const WIDTH     = 'width';
    const HEIGHT    = 'height';

    const SIZE_DIMENSIONS = [
        self::SIZE_MIN  => [
            self::WIDTH     => 2000,
            self::HEIGHT    => 2000,
        ],
        self::SIZE_MED  => [
            self::WIDTH     => 2000,
            self::HEIGHT    => 2000,
        ],
        self::SIZE_MAX  => [
            self::WIDTH     => 2000,
            self::HEIGHT    => 2000,
        ]
    ];

    protected ImageEntity $entity;
    protected static ?string $buildFileExtension    = null;
    protected ?string $lastBuiltImageMimeType       = null;


    public function __construct(
        protected ImageUrlGenerator $urlGenerator, protected EntityManagerInterface $em, protected ProjectDir $projectDir
    )
    {
        $this->entity = new ImageEntity();
        if( empty(static::$buildFileExtension) ) {
            static::setBestFormatFromBrowserSupport();
        }
    }


    public function getSizes() : array
    {
        return [static::SIZE_MIN, static::SIZE_MED, static::SIZE_MAX];
    }


    public function checkSize(string $size) : static
    {
        if( !in_array($size, $this->getSizes()) ) {
            throw new ImageLogicException("Unknown image size");
        }

        return $this;
    }


    public function getOriginalFileName() : string
    {
        $imageId = $this->entity->getId();
        if( empty($imageId) ) {
            throw new ImageLogicException("Cannot get the name of an Image without ID");
        }

        $format = $this->entity->getFormat();
        if( empty($format) ) {
            throw new ImageLogicException("Cannot get the name of an Image without format");
        }

        $imageFileName = implode('.', [$imageId, $format]);

        return $imageFileName;
    }


    public function getOriginalFilePath() : string
    {
        $imageFolderMod = $this->getFolderMod();
        $fileName       = $this->getOriginalFileName();
        $imageFilePath  = $this->projectDir->createVarDirFromFilePath(
            static::UPLOADED_IMAGES_FOLDER_NAME . "/originals/$imageFolderMod/$fileName"
        );

        return $imageFilePath;
    }


    public function getFolderMod() : int
    {
        $imageId = $this->entity->getId();
        if( empty($imageId) ) {
            throw new ImageLogicException("Cannot get the FolderMod of an Image without ID");
        }

        $imageFolderMod = (int)ceil($imageId / static::HOW_MANY_FILES_PER_FOLDER);
        return $imageFolderMod;
    }


    public function getBuiltFileName() : string
    {
        $fileName = $this->getOriginalFileName();

        if( !empty(static::$buildFileExtension) ) {
            $fileName = pathinfo($fileName, PATHINFO_FILENAME) . "." . static::$buildFileExtension;
        }

        return $fileName;
    }


    protected function getBuiltFilePath(string $size) : string
    {
        $this->checkSize($size);

        $imageFolderMod = $this->getFolderMod();
        $fileName       = $this->getBuiltFileName();

        $imageFilePath = $this->projectDir->createVarDirFromFilePath(
            static::UPLOADED_IMAGES_FOLDER_NAME . "/cache/$size/$imageFolderMod/$fileName"
        );

        return $imageFilePath;
    }


    public function tryPreBuilt(string $size) : bool
    {
        $builtFilePath = $this->getBuiltFilePath($size);
        $exists = file_exists($builtFilePath);
        $this->lastBuiltImageMimeType = $exists ? mime_content_type($builtFilePath) : null;
        return $exists;
    }


    public function build(string $size) : static
    {
        $originalFilePath = $this->getOriginalFilePath();

        // https://symfony.com/doc/current/the-fast-track/en/23-imagine.html#optimizing-images-with-imagine
        list($iwidth, $iheight) = getimagesize($originalFilePath);
        $ratio = $iwidth / $iheight;

        $width  = static::SIZE_DIMENSIONS[$size][static::WIDTH];
        $height = static::SIZE_DIMENSIONS[$size][static::HEIGHT];

        if ($width / $height > $ratio) {
            $width = $height * $ratio;
        } else {
            $height = $width / $ratio;
        }

        // https://github.com/php-imagine/Imagine
        $phpImagine = (new Imagine())->open($originalFilePath);
        $phpImagine->resize(new Box($width, $height));

        //
        $outputFilePath =
            $this
                ->applyWatermark($phpImagine, $size)
                ->getBuiltFilePath($size);

        $phpImagine->save($outputFilePath, [
            'flatten'               => false,
            'jpeg_quality'          => '80',
            'png_compression_level' => 9,
            'avif_quality'          => 30,
        ]);

        $this->lastBuiltImageMimeType = mime_content_type($outputFilePath);

        return $this;
    }


    public function applyWatermark(ImageInterface $phpImagine, string $size) : static
    {
        $this->checkSize($size);
        $watermarkPosition = $this->entity->getWatermarkPosition();

        if(
            in_array($size, [static::SIZE_MIN]) ||
            $watermarkPosition == ImageEntity::WATERMARK_DISABLED
        ) {
            return $this;
        }

        $watermarkFilePath = $this->projectDir->getPublicDirFromFilePath(static::WATERMARK_FILEPATH);

        $watermark  = (new Imagine())->open($watermarkFilePath);

        $imageW     = $phpImagine->getSize()->getWidth();
        $imageH     = $phpImagine->getSize()->getHeight();

        $watermW    = $watermark->getSize()->getWidth();
        $watermH    = $watermark->getSize()->getHeight();

        $newWatermW = floor( $imageW / 100 * static::WATERMARK_COVERAGE_PERCENT );
        $newWatermH = floor( $watermH *  $newWatermW / $watermW );

        $watermark->resize(new Box($newWatermW, $newWatermH));

        // TODO zaneeee!! Gestire posizione watermark da DB

        $bottomLeft = new Point(0, $imageH - $newWatermH);

        $phpImagine->paste($watermark, $bottomLeft);
        return $this;
    }


    public function getBuiltImageMimeType() : ?string
    {
        return $this->lastBuiltImageMimeType;
    }


    public function getXSendPath(string $size) : string
    {
        $xSendPath =
            DIRECTORY_SEPARATOR .
            implode(DIRECTORY_SEPARATOR, [
                static::UPLOADED_ASSET_XSEND_PATH, 'images', 'cache', $size, $this->getFolderMod(), $this->getBuiltFileName()
            ]);

        return $xSendPath;
    }


    public function getContent(string $size) : string
    {
        $result = $this->tryPreBuilt($size);

        if( !$result ) {
            $this->build($size);
        }

        $filePath   = $this->getBuiltFilePath($size);
        $data       = file_get_contents($filePath);
        return $data;
    }


    public static function setBestFormatFromBrowserSupport() : ?string
    {
        $httpAccept = $_SERVER['HTTP_ACCEPT'] ?? '';

        foreach([ImageEntity::FORMAT_AVIF, ImageEntity::FORMAT_WEBP] as $format) {

            $bestFormatMimeType = 'image/' . $format;
            $isSupported = stripos($httpAccept, $bestFormatMimeType) !== false;

            if($isSupported) {

                static::$buildFileExtension = $format;
                return $format;
            }
        }

        static::$buildFileExtension = null;
        return null;
    }


    public function getEntity() : ImageEntity { return parent::getEntity(); }

    /**
     * @param ImageEntity $entity
     */
    public function setEntity(BaseEntity $entity) : static { return parent::setEntity($entity); }

    public function getTitle() : ?string { return $this->entity->getTitle(); }
    public function getFormat() : ?string { return $this->entity->getFormat(); }

    public function getUrl(string $size) : string { return $this->urlGenerator->generateUrl($this, $size); }
    public function getShortUrl(string $size) : string { return $this->urlGenerator->generateShortUrl($this, $size); }
}
