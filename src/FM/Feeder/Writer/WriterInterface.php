<?php

namespace FM\Feeder\Writer;

interface WriterInterface
{
    public function __construct(\SplFileObject $file);
    public function setFile(\SplFileObject $file);
    public function start();
    public function write($data);
    public function flush();
    public function end();
}
