<?php
namespace App\ServiceCollection\Cms;

use App\Service\Cms\File as FileService;
use App\Entity\Cms\File as FileEntity;


class FileCollection extends BaseCmsServiceCollection
{
    const string ENTITY_CLASS = FileService::ENTITY_CLASS;


    public function createService(?FileEntity $entity = null) : FileService { return $this->factory->createFile($entity); }
}
