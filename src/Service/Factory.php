<?php
namespace App\Service;

use App\Entity\Cms\Article as ArticleEntity;
use App\Entity\Cms\File as FileEntity;
use App\Entity\Cms\Image as ImageEntity;
use App\Entity\Cms\Tag as TagEntity;
use App\Entity\PhpBB\Topic as TopicEntity;
use App\Service\Cms\Article as ArticleService;
use App\Service\Cms\ArticleEditor;
use App\Service\Cms\ArticleUrlGenerator;
use App\Service\Cms\File as FileService;
use App\Service\Cms\FileUrlGenerator;
use App\Service\Cms\Image as ImageService;
use App\Service\Cms\ImageUrlGenerator;
use App\Service\Cms\Tag as TagService;
use App\Service\Cms\TagUrlGenerator;
use App\ServiceCollection\PhpBB\TopicCollection;
use App\Service\PhpBB\ForumUrlGenerator;
use App\Service\PhpBB\Topic as TopicService;
use App\ServiceCollection\Cms\ArticleCollection;
use App\ServiceCollection\Cms\FileCollection;
use App\ServiceCollection\Cms\ImageCollection;
use App\ServiceCollection\Cms\TagCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use TurboLabIt\BaseCommand\Service\ProjectDir;
use App\Entity\PhpBB\User as UserEntity;
use App\Service\User as UserService;
use App\ServiceCollection\UserCollection;


class Factory
{
    protected ?ImageService $defaultSpotlight;
    protected ?TagService $defaultTag;
    protected ?User $currentUser;


    //<editor-fold defaultstate="collapsed" desc="*** __construct ***">
    public function __construct(
        protected EntityManagerInterface $em, protected ProjectDir $projectDir, protected Security $security,
        protected ArticleUrlGenerator $articleUrlGenerator,
        protected TagUrlGenerator $tagUrlGenerator,
        protected ImageUrlGenerator $imageUrlGenerator,
        protected FileUrlGenerator $fileUrlGenerator,
        protected ForumUrlGenerator $forumUrlGenerator,
        protected UserUrlGenerator $userUrlGenerator,
    )
    { }
    //</editor-fold>


    public function getEntityManager() : EntityManagerInterface { return $this->em; }

    public function getProjectDir() : ProjectDir { return $this->projectDir; }

    public function getCurrentUser() : ?UserService
    {
        if( !empty($this->currentUser) ) {
            return $this->currentUser;
        }

        $entity = $this->security->getUser();
        if( empty($entity) ) {
            return $this->currentUser = null;
        }

        return $this->currentUser = $this->createUser($entity);
    }


    //<editor-fold defaultstate="collapsed" desc="*** Article ***">
    public function createArticle(?ArticleEntity $entity = null) : ArticleService
    {
        $service = new ArticleService($this);
        if( !empty($entity) ) {
            $service->setEntity($entity);
        }

        return $service;
    }


    public function createArticleEditor(?ArticleEntity $entity = null) : ArticleEditor
    {
        $service = new ArticleEditor($this);
        if( !empty($entity) ) {
            $service->setEntity($entity);
        }

        return $service;
    }


    public function getArticleUrlGenerator() : ArticleUrlGenerator { return $this->articleUrlGenerator; }

    public function createArticleCollection() : ArticleCollection { return new ArticleCollection($this); }
    //</editor-fold>


    //<editor-fold defaultstate="collapsed" desc="*** Tag ***">
    public function createDefaultTag() : TagService
    {
        if( !empty($this->defaultTag) ) {
            return $this->defaultTag;
        }

        $entity =
            (new TagEntity())
                ->setId(TagService::ID_DEFAULT_TAG)
                ->setTitle("PC");

        return $this->defaultTag = $this->createTag($entity);
    }


    public function createTag(?TagEntity $entity = null) : TagService
    {
        $service = new TagService($this);
        if( !empty($entity) ) {
            $service->setEntity($entity);
        }

        return $service;
    }


    public function getTagUrlGenerator() : TagUrlGenerator { return $this->tagUrlGenerator; }

    public function createTagCollection() : TagCollection { return new TagCollection($this); }
    //</editor-fold>


    //<editor-fold defaultstate="collapsed" desc="*** Image ***">
    public function createDefaultSpotlight() : ImageService
    {
        if( !empty($this->defaultSpotlight) ) {
            return $this->defaultSpotlight;
        }

        $entity =
            (new ImageEntity())
                ->setId(ImageService::ID_DEFAULT_SPOTLIGHT)
                ->setFormat(ImageEntity::FORMAT_PNG)
                ->setWatermarkPosition(ImageEntity::WATERMARK_DISABLED)
                ->setTitle("TurboLab.it");

        $this->defaultSpotlight = $this->createImage($entity);
        return $this->defaultSpotlight;
    }


    public function createImage(?ImageEntity $entity = null) : ImageService
    {
        $service = new ImageService($this);
        if( !empty($entity) ) {
            $service->setEntity($entity);
        }

        return $service;
    }


    public function getImageUrlGenerator() : ImageUrlGenerator { return $this->imageUrlGenerator; }


    public function createImageCollection() : ImageCollection { return new ImageCollection($this); }
    //</editor-fold>


    //<editor-fold defaultstate="collapsed" desc="*** File ***">
    public function createFile(?FileEntity $entity = null) : FileService
    {
        $service = new FileService($this);
        if( !empty($entity) ) {
            $service->setEntity($entity);
        }

        return $service;
    }


    public function createFileCollection() : FileCollection { return new FileCollection($this); }

    public function getFileUrlGenerator() : FileUrlGenerator { return $this->fileUrlGenerator; }
    //</editor-fold>


    //<editor-fold defaultstate="collapsed" desc="*** Topic ***">
    public function createTopic(?TopicEntity $entity = null) : TopicService
    {
        $service = new TopicService($this);
        if( !empty($entity) ) {
            $service->setEntity($entity);
        }

        return $service;
    }


    public function createTopicCollection() : TopicCollection { return new TopicCollection($this); }

    public function getForumUrlGenerator() : ForumUrlGenerator { return $this->forumUrlGenerator; }
    //</editor-fold>


    //<editor-fold defaultstate="collapsed" desc="*** User ***">
    public function createUser(?UserEntity $entity = null) : UserService
    {
        $service = new UserService($this);
        if( !empty($entity) ) {
            $service->setEntity($entity);
        }

        return $service;
    }


    public function createUserCollection() : UserCollection { return new UserCollection($this); }

    public function getUserUrlGenerator() : UserUrlGenerator { return $this->userUrlGenerator; }
    //</editor-fold>
}
