<?php
namespace App\Entity\Cms;

use App\Exception\InvalidEnumException;
use App\Repository\Cms\ImageRepository;
use App\Trait\IdableEntityTrait;
use App\Trait\TitleableEntityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: ImageRepository::class)]
class Image extends BaseCmsEntity
{
    const WATERMARK_DISABLED        = 0;
    const WATERMARK_TOP_LEFT        = 1;
    const WATERMARK_TOP_RIGHT       = 2;
    const WATERMARK_BOTTOM_RIGHT    = 3;
    const WATERMARK_BOTTOM_LEFT     = 4;

    const FORMAT_JPG    = 'jpg';
    const FORMAT_PNG    = 'png';
    const FORMAT_WEBP   = 'webp';
    const FORMAT_AVIF   = 'avif';

    use IdableEntityTrait, TitleableEntityTrait;

    #[ORM\Column(length: 5)]
    protected ?string $format = null;

    #[ORM\Column(type: Types::SMALLINT, options: ['unsigned' => true])]
    protected int $watermarkPosition = self::WATERMARK_BOTTOM_LEFT;

    #[ORM\Column]
    protected ?bool $reusable = false;

    #[ORM\OneToMany(mappedBy: 'image', targetEntity: ImageAuthor::class, orphanRemoval: true, cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['ranking' => 'ASC'])]
    protected Collection $authors;

    #[ORM\OneToMany(mappedBy: 'image', targetEntity: ArticleImage::class, orphanRemoval: true, cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['ranking' => 'ASC'])]
    protected Collection $articles;

    #[ORM\OneToMany(mappedBy: 'spotlight', targetEntity: Article::class)]
    #[ORM\OrderBy(['createdAt' => 'ASC'])]
    protected Collection $spotlightForArticles;


    public function __construct()
    {
        $this->authors              = new ArrayCollection();
        $this->articles             = new ArrayCollection();
        $this->spotlightForArticles = new ArrayCollection();
    }


    public static function getFormats() : array
    {
        // from best to worst
        return [static::FORMAT_AVIF, static::FORMAT_WEBP, static::FORMAT_PNG, static::FORMAT_JPG];
    }

    public function getFormat(): ?string
    {
        return $this->format;
    }

    public function setFormat(string $format): static
    {
        if( !in_array($format, static::getFormats() ) ) {
            throw new InvalidEnumException("Invalid image format");
        }

        $this->format = $format;
        return $this;
    }


    public function getWatermarkPositions() : array
    {
        return [
            static::WATERMARK_DISABLED,
            static::WATERMARK_TOP_LEFT, static::WATERMARK_TOP_RIGHT,
            static::WATERMARK_BOTTOM_RIGHT, static::WATERMARK_BOTTOM_LEFT
        ];
    }

    public function getWatermarkPosition(): ?int
    {
        return $this->watermarkPosition;
    }

    public function setWatermarkPosition(int $watermarkPosition): static
    {
        if( !in_array($watermarkPosition, $this->getWatermarkPositions() ) ) {
            throw new InvalidEnumException("Invalid watermark position");
        }

        $this->watermarkPosition = $watermarkPosition;
        return $this;
    }

    public function isReusable(): ?bool
    {
        return $this->reusable;
    }

    public function setReusable(bool $reusable): static
    {
        $this->reusable = $reusable;
        return $this;
    }

    /**
     * @return Collection<int, ImageAuthor>
     */
    public function getAuthors(): Collection
    {
        return $this->authors;
    }

    public function addAuthor(ImageAuthor $author): static
    {
        $currentItems = $this->getAuthors();
        foreach($currentItems as $item) {

            if( $item->getUser()->getId() == $author->getUser()->getId() ) {
                return $this;
            }
        }

        $this->authors->add($author);
        $author->setImage($this);

        return $this;
    }

    public function removeAuthor(ImageAuthor $author): static
    {
        if ($this->authors->removeElement($author)) {
            // set the owning side to null (unless already changed)
            if ($author->getImage() === $this) {
                $author->setImage(null);
            }
        }

        return $this;
    }


    /**
     * @return Collection<int, ArticleImage>
     */
    public function getArticles(): Collection
    {
        return $this->articles;
    }

    public function addArticle(ArticleImage $article): static
    {
        $currentItems = $this->getArticles();
        foreach($currentItems as $item) {

            if( $item->getArticle()->getId() == $article->getArticle()->getId() ) {
                return $this;
            }
        }

        $this->articles->add($article);
        $article->setImage($this);

        return $this;
    }

    public function removeArticle(ArticleImage $article): static
    {
        if ($this->articles->removeElement($article)) {
            // set the owning side to null (unless already changed)
            if ($article->getImage() === $this) {
                $article->setImage(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Article>
     */
    public function getSpotlightForArticles(): Collection
    {
        return $this->spotlightForArticles;
    }

    public function addCoverForArticle(Article $coverForArticle): static
    {
        if (!$this->spotlightForArticles->contains($coverForArticle)) {
            $this->spotlightForArticles->add($coverForArticle);
            $coverForArticle->setSpotlight($this);
        }

        return $this;
    }

    public function removeCoverForArticle(Article $coverForArticle): static
    {
        if ($this->spotlightForArticles->removeElement($coverForArticle)) {
            // set the owning side to null (unless already changed)
            if ($coverForArticle->getSpotlight() === $this) {
                $coverForArticle->setSpotlight(null);
            }
        }

        return $this;
    }
}
