<?php

namespace FM\Feeder\Writer;

use XmlWriter as BaseXmlWriter;

class XmlWriter implements WriterInterface
{
    protected $file;
    protected $writer;
    protected $ident = false;
    protected $rootNode = 'feed';

    public function __construct(\SplFileObject $file = null)
    {
        $this->file = $file;
    }

    public function __clone()
    {
        $this->file = null;
        $this->writer = null;
    }

    public function setFile(\SplFileObject $file)
    {
        $this->file = $file;
    }

    public function setIdent($ident)
    {
        $this->ident = (boolean) $ident;
    }

    public function setRootNode($node)
    {
        $this->rootNode = $node;
    }

    public function start()
    {
        if ($this->writer) {
            throw new \RuntimeException('Writer already started');
        }

        $this->writer = new BaseXmlWriter();
        $this->writer->openUri($this->file->getPathname());
        $this->writer->setIndent($this->ident);
        $this->writer->startDocument('1.0', 'UTF-8');
        $this->write(sprintf('<%s>', $this->rootNode));
    }

    public function write($data)
    {
        if (!$this->writer) {
            throw new \RuntimeException('Start writer first');
        }

        $this->writer->writeRaw($data);
    }

    public function flush()
    {
        if (!$this->writer) {
            throw new \RuntimeException('Start writer first');
        }

        $this->writer->flush();
    }

    public function end()
    {
        if (!$this->writer) {
            throw new \RuntimeException('Start writer first');
        }

        $this->write(sprintf('</%s>', $this->rootNode));
        $this->writer->endDocument();
        $this->flush();

        $this->writer = null;
    }
}
