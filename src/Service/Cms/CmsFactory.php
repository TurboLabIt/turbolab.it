<?php
namespace App\Service\Cms;

use Doctrine\ORM\EntityManagerInterface;
use TurboLabIt\BaseCommand\Service\ProjectDir;
use App\Entity\Cms\Article as ArticleEntity;
use App\Service\Cms\Article as ArticleService;
use App\Entity\Cms\Tag as TagEntity;
use App\Service\Cms\Tag as TagService;
use App\Entity\Cms\Image as ImageEntity;
use App\Service\Cms\Image as ImageService;

class CmsFactory
{
    protected ?ImageService $defaultSpotlight;


    public function __construct(
        protected EntityManagerInterface $em, protected ProjectDir $projectDir,
        protected ArticleUrlGenerator $articleUrlGenerator,
        protected TagUrlGenerator $tagUrlGenerator,
        protected ImageUrlGenerator $imageUrlGenerator
    )
    { }


    public function createArticle(?ArticleEntity $entity = null) : ArticleService
    {
        $service = new ArticleService($this->articleUrlGenerator, $this->em, $this);
        if( !empty($entity) ) {
            $service->setEntity($entity);
        }

        return $service;
    }


    public function createTag(?TagEntity $entity = null) : TagService
    {
        $service = new TagService($this->tagUrlGenerator, $this->em, $this);
        if( !empty($entity) ) {
            $service->setEntity($entity);
        }

        return $service;
    }


    public function createImage(?ImageEntity $entity = null) : ImageService
    {
        $service = new ImageService($this->imageUrlGenerator, $this->em, $this->projectDir);
        if( !empty($entity) ) {
            $service->setEntity($entity);
        }

        return $service;
    }


    public function createDefaultSpotlight() : ImageService
    {
        if( !empty($this->defaultSpotlight) ) {
            return $this->defaultSpotlight;
        }

        $entity =
            (new ImageEntity())
                ->setId(1)
                ->setFormat('png')
                ->setWatermarkPosition(ImageEntity::WATERMARK_DISABLED)
                ->setTitle("TurboLab.it");

        $this->defaultSpotlight = $this->createImage($entity);
        return $this->defaultSpotlight;
    }
}
