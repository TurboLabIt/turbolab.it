<?php
namespace App\Service\Cms;

use App\Service\UrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


class FileUrlGenerator extends UrlGenerator
{
    public function generateUrl(File $file, int $urlType = UrlGeneratorInterface::ABSOLUTE_URL) : string
    {
        $fileUrl =
            $this->symfonyUrlGenerator->generate('app_file', [
                "fileId" => $file->getId()
            ], $urlType);

        return $fileUrl;
    }


    public function isUrl(string $urlCandidate) : bool
    {
        if( !$this->isInternalUrl($urlCandidate) ) {
            return false;
        }

        $urlPath = $this->removeDomainFromUrl($urlCandidate);
        if( empty($urlPath) ) {
            return false;
        }

        $match = preg_match('/^\/scarica\/[1-9]+[0-9]*$/', $urlPath);
        return (bool)$match;
    }
}
