<?php
namespace App\Controller;

use App\Service\Cms\HtmlProcessor;
use App\ServiceCollection\Cms\ArticleCollection;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Twig\Environment;


class FeedController extends BaseController
{
    protected Request $httpRequest;


    public function __construct(
        protected ArticleCollection $articleCollection,
        RequestStack $requestStack, protected TagAwareCacheInterface $cache, protected ParameterBagInterface $parameterBag,
        protected Environment $twig
    )
    {
        $this->httpRequest = $requestStack->getCurrentRequest();
    }


    #[Route('/feed', name: 'app_feed')]
    public function main() : Response
    {
        $arrData    = [
            "title"         => "TurboLab.it | Feed Principale",
            "selfUrl"       => $this->getSelfUrl(),
            "fullFeed"      => false,
            "Articles"      => $this->articleCollection->loadLatestPublished(),
            "description"   => 'Questo feed eroga i più recenti contenuti pubblicati in home page'
        ];

        return $this->responseAsRss($arrData);
    }


    #[Route('/feed/nuovi-finiti', name: 'app_feed_new_unpublished')]
    public function newUnpublished(): Response
    {
        $arrData    = [
            "title"         => "TurboLab.it | Nuovi contenuti completati, in attesa di pubblicazione",
            "selfUrl"       => $this->getSelfUrl(),
            "fullFeed"      => false,
            "Articles"      => $this->articleCollection->loadLatestReadyForReview(),
            "description"   => 'Questo feed eroga i contenuti che gli autori hanno indicato come "finiti", ma non ancora pubblicati',
        ];

        return $this->responseAsRss($arrData);
    }


    #[Route('/feed/fullfeed', name: 'app_feed_fullfeed')]
    public function fullFeed(HtmlProcessor $htmlProcessor): Response
    {
        $arrData    = [
            "title"         => "TurboLab.it | Full Feed",
            "selfUrl"       => $this->getSelfUrl(),
            "fullFeed"      => true,
            "Articles"      => $this->articleCollection->loadLatestPublished()->setHtmlProcessor($htmlProcessor),
            "description"   => 'Questo feed eroga integralmente i contenuti che appaiono in home page'
        ];

        return $this->responseAsRss($arrData);
    }


    public function getSelfUrl()
    {
        return strtok($this->httpRequest->getUri(), '?');
    }


    protected function responseAsRss(array $arrData)
    {
        $response = $this->render('feed/rss.xml.twig', $arrData);
        /**
         * 📚 https://validator.w3.org/feed/docs/warning/UnexpectedContentType.html
         * RSS feeds should be served as application/rss+xml. Alternatively,
         * for compatibility with widely-deployed web browsers, [...] application/xml
         */
        $response->headers->set('Content-Type', 'application/xml');
        return $response;
    }
}
