<?php
namespace App\ServiceCollection\Cms;

use App\Service\Cms\Article as ArticleService;
use App\Entity\Cms\Article as ArticleEntity;
use App\Service\Cms\CmsFactory;
use Doctrine\ORM\EntityManagerInterface;


class ArticleCollection extends BaseCmsServiceCollection
{
    const ENTITY_CLASS = ArticleService::ENTITY_CLASS;


    public function __construct(protected EntityManagerInterface $em, protected CmsFactory $factory)
    { }


    public function loadLatestPublished(int $num) : static
    {
        $arrArticles = $this->em->getRepository(ArticleEntity::class)->findLatestPublished($num);
        foreach($arrArticles as $articleId => $articleEntity) {
            $this->arrData[$articleId] = $this->createService($articleEntity);
        }

        return $this;
    }


    public function createService(?ArticleEntity $entity = null) : ArticleService { return $this->factory->createArticle($entity); }
}
