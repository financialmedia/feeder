<?php

namespace FM\Feeder\Reader;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\CustomNormalizer;
use FM\Feeder\Resource\Resource;
use FM\Feeder\Resource\ResourceCollection;

class XmlReader extends AbstractReader
{
    /**
     * @var \XMLReader
     */
    protected $reader;

    /**
     * @var Symfony\Component\Serializer\Serializer
     */
    protected $serializer;

    /**
     * @var integer
     */
    protected $key;

    public function __construct($nextNode, ResourceCollection $resources = null, EventDispatcher $dispatcher = null)
    {
        parent::__construct($nextNode, $resources, $dispatcher);

        $this->serializer = new Serializer(
            array(new CustomNormalizer()),
            array('xml' => new XmlEncoder())
        );
    }

    protected function doKey()
    {
        return $this->key;
    }

    protected function doCurrent()
    {
        return $this->reader->readOuterXml();
    }

    protected function doNext()
    {
        $this->moveToNextNode($this->reader);
    }

    protected function doRewind()
    {
        $this->reader->close();
        $this->reader->open($this->resource->getFile()->getPathname(), 'UTF-8', LIBXML_NOENT | LIBXML_PARSEHUGE);

        $this->key = -1;

        $this->next();
    }

    protected function doValid()
    {
        return (boolean) $this->doCurrent();
    }

    protected function getNextNodeCallback($nextNode)
    {
        if ($nextNode instanceof \Closure) {
            return $nextNode;
        }

        if (!is_string($nextNode)) {
            throw new \InvalidArgumentException('Expecting a string of callback for nextNode');
        }

        $nodeName = mb_strtolower($nextNode);

        return function(\XMLReader $reader) use ($nodeName) {
            while ($reader->read()) {
                if (($reader->nodeType === \XMLReader::ELEMENT) && (mb_strtolower($reader->name) === $nodeName)) {
                    return true;
                }
            }

            return false;
        };
    }

    protected function moveToNextNode(\XMLReader $reader)
    {
        if (!$this->nextNode instanceof \Closure) {
            throw new \LogicException('No function set to get next node');
        }

        $this->key++;

        return call_user_func($this->nextNode, $reader);
    }

    protected function createReader(Resource $resource)
    {
        $this->reader = new \XmlReader();
        $this->reader->open($resource->getFile()->getPathname(), 'UTF-8', LIBXML_NOENT | LIBXML_PARSEHUGE);

        $this->key = -1;
        $this->doNext();
    }

    protected function serialize($xml)
    {
        return new ParameterBag((array) $this->serializer->decode($xml, 'xml'));
    }
}
