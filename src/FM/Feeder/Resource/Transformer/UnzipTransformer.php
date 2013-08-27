<?php

namespace FM\Feeder\Resource\Transformer;

use FM\Feeder\Resource\Resource;
use FM\Feeder\Resource\ResourceCollection;

class UnzipTransformer implements ResourceTransformer
{
    protected $target;
    protected $filename;

    /**
     * @param string $filename The filename in the zip file to return
     * @param string $target   Target directory
     */
    public function __construct($filename, $target = null)
    {
        $this->filename = $filename;
        $this->target = $target ?: sys_get_temp_dir();
    }

    public function transform(Resource $resource, ResourceCollection $collection)
    {
        $file = $resource->getFile();

        $zip = new \ZipArchive();
        $zip->open($file->getPathname());
        $zip->extractTo($this->target);
        $resource->setFile(new \SplFileObject($this->getTargetFile()));
    }

    public function needsTransforming(Resource $resource)
    {
        $targetFile = $this->getTargetFile();

        if (!file_exists($targetFile)) {
            return true;
        }

        return $resource->getFile()->getMTime() > filemtime($targetFile);
    }

    public function getTargetFile()
    {
        return sprintf('%s/%s', $this->target, $this->filename);
    }
}
