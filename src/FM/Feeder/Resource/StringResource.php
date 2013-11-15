<?php

namespace FM\Feeder\Resource;

class StringResource implements Resource
{
    protected $data;
    protected $file;

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

    public function setFile(\SplFileObject $file)
    {
        $this->file = $file;
    }

    public function getFile()
    {
        if ($this->file === null) {
            try {
                $this->file = new TempFile();
                $this->file->fwrite($this->data);
                $this->file->fseek(0);
            } catch (\RuntimeException $e) {
                throw new TransportException($e->getMessage(), null, $e);
            }
        }

        return $this->file;
    }
}
