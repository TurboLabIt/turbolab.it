<?php
namespace App\Service\Cms;

use App\Entity\Cms\File as FileEntity;
use App\Exception\FileLogicException;
use App\Service\Factory;
use App\Trait\UrlableServiceTrait;
use App\Trait\ViewableServiceTrait;
use Doctrine\ORM\EntityManagerInterface;
use TurboLabIt\BaseCommand\Service\ProjectDir;


class File extends BaseCmsService
{
    const string ENTITY_CLASS          = FileEntity::class;
    const string NOT_FOUND_EXCEPTION   = 'App\Exception\FileNotFoundException';

    const string UPLOADED_FILES_FOLDER_NAME = parent::UPLOADED_ASSET_FOLDER_NAME . "/files";

    use ViewableServiceTrait;
    use UrlableServiceTrait;


    public function __construct(
        FileUrlGenerator $urlGenerator, protected EntityManagerInterface $em, protected Factory $factory,
        protected ProjectDir $projectDir
    )
    {
        $this->clear();
        $this->urlGenerator = $urlGenerator;
    }

    //<editor-fold defaultstate="collapsed" desc="*** 🗄️ Database ORM entity ***">
    public function getEntity() : ?FileEntity { return $this->entity; }
    //</editor-fold>

    //<editor-fold defaultstate="collapsed" desc="*** 👽 Local or remote hosted ***">
    public function getExternalDownloadUrl() : ?string { return $this->entity->getUrl(); }

    public function isLocal() : bool { return empty( $this->getExternalDownloadUrl() ); }

    public function isExternal() : bool { return !$this->isLocal(); }
    //</editor-fold>


    //<editor-fold defaultstate="collapsed" desc="*** 🔎 File type ***">
    public function getMimeType() : ?string
    {
        if( !$this->isLocal() ) {
            return null;
        }

        $filePath = $this->getOriginalFilePath();
        $mime = mime_content_type($filePath);
        return $mime;
    }


    public function isBitTorrent() : bool
    {
        $format = $this->entity->getFormat();
        return stripos($format, 'torrent') !== false || stripos($format, 'magnet') !== false;
    }


    public function getCompatibilities() : array
    {
        $format = $this->entity->getFormat();
        if( empty($format) ) {
            return [];
        }

        $arrWindowsFormats = ['exe', 'msi', 'bat', 'microsoft store', 'cmd', 'ps', 'appx'];
        if( in_array($format, $arrWindowsFormats) ) {
            return [[
                "name"  => "Windows",
                "slug"  => "windows",
                "color" => "#74C0FC"
            ]];
        }

        $arrLinuxFormats = ['service', 'sh'];
        if( in_array($format, $arrLinuxFormats) ) {
            return [[
                "name"  => "Linux",
                "slug"  => "linux",
                "color" => "#000000"
            ]];
        }

        $arrAndroidFormats = ['apk'];
        if( in_array($format, $arrAndroidFormats) ) {
            return [[
                "name"  => "Android",
                "slug"  => "android",
                "color" => "#3DDC84"
            ]];
        }


        return [];
    }
    //</editor-fold>


    public function getOriginalFileName() : ?string
    {
        if( !$this->isLocal() ) {
            return null;
        }

        $fileId = $this->entity->getId();
        if( empty($fileId) ) {
            throw new FileLogicException("Cannot get the name of a File without ID");
        }

        $format = $this->entity->getFormat();
        if( empty($format) ) {
            throw new FileLogicException("Cannot get the name of a File without format");
        }

        $fileName = implode('.', [$fileId, $format]);
        return $fileName;
    }


    public function getOriginalFilePath() : ?string
    {
        if( !$this->isLocal() ) {
            return null;
        }

        $fileName = $this->getOriginalFileName();
        $filePath = $this->projectDir->createVarDirFromFilePath(
            static::UPLOADED_FILES_FOLDER_NAME . "/$fileName"
        );

        return $filePath;
    }


    public function getXSendPath() : string
    {
        $xSendPath =
            DIRECTORY_SEPARATOR .
            implode(DIRECTORY_SEPARATOR, [
                static::UPLOADED_ASSET_XSEND_PATH, 'files', $this->getOriginalFileName()
            ]);

        return $xSendPath;
    }


    public function getContent() : string
    {
        $filePath   = $this->getOriginalFilePath();
        $data       = file_get_contents($filePath);
        return $data;
    }


    public function getUserPerceivedFileName() : string
    {
        $fileTitle = $this->getTitle();
        if( empty($fileTitle) ) {
            throw new FileLogicException("Cannot get the name of a File without title");
        }

        $format = $this->entity->getFormat();
        if( empty($format) ) {
            throw new FileLogicException("Cannot get the name of a File without format");
        }

        $fileTitle = html_entity_decode($fileTitle, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Remove anything which isn't a word, whitespace, number or any of the following caracters -_~,;[]().
        $fileName = mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", ' ', $fileTitle);
        $fileName = trim($fileName);
        $fileName = preg_replace('/\s+/', ' ', $fileName);
        $fileName = trim($fileName);

        $fileName .= "." . $format;
        return $fileName;
    }
}
