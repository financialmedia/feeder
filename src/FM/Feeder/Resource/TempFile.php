<?php

namespace FM\Feeder\Resource;

final class TempFile extends \SplFileObject
{
    protected $fileName;

    public function __construct()
    {
        $this->fileName = tempnam(sys_get_temp_dir(), 'feeder');

        parent::__construct($this->fileName, 'a+');
    }

    public function __destruct()
    {
        if (file_exists($this->fileName)) {
            unlink($this->fileName);
        }
    }
}
