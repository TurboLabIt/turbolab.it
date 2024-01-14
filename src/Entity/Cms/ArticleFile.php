<?php
namespace App\Entity\Cms;

use App\Entity\User;
use App\Repository\Cms\ArticleFileRepository;
use App\Trait\RankableEntityTrait;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: ArticleFileRepository::class)]
class ArticleFile extends BaseCmsEntity
{
    #[ORM\ManyToOne(inversedBy: 'files')]
    #[ORM\JoinColumn(nullable: false)]
    protected ?Article $article = null;

    #[ORM\ManyToOne(inversedBy: 'articles')]
    #[ORM\JoinColumn(nullable: false)]
    protected ?File $file = null;

    #[ORM\ManyToOne(inversedBy: 'articlesAttachedToFiles')]
    private ?User $user = null;

    use RankableEntityTrait;


    public function getArticle(): ?Article
    {
        return $this->article;
    }

    public function setArticle(?Article $article): static
    {
        $this->article = $article;
        return $this;
    }

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function setFile(?File $file): static
    {
        $this->file = $file;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }
}
