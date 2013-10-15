<?php

namespace FM\Feeder\Resource;

class StringResource implements Resource
{
    protected $data;

    public function __construct($data)
    {
        if (!is_string($data)) {
            throw new \InvalidArgumentException('You must pass a string to a StringResource');
        }

        $this->data = $data;
    }

    public function getTransport()
    {
        return null;
    }

    public function getFile()
    {
        $file = new TempFile();
        $file->fwrite($this->data);
        $file->fseek(0);

        return $file;
    }
}
