<?php

namespace FM\Feeder\Reader;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\CustomNormalizer;
use FM\Feeder\Resource\Resource;
use FM\Feeder\Exception\ReadException;

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

    public function __construct($nextNode, $resources = null, EventDispatcher $dispatcher = null)
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
        $xml = $this->reader->readOuterXml();

        if ($error = $this->getXmlError()) {
            throw new ReadException($error);
        }

        return $xml;
    }

    protected function doNext()
    {
        $this->moveToNextNode($this->reader);
    }

    protected function doRewind()
    {
        $this->reader->close();
        $this->reader->open($this->resource->getFile()->getPathname(), 'UTF-8', LIBXML_NOENT | LIBXML_PARSEHUGE | LIBXML_NOERROR | LIBXML_NOWARNING);

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
            $found = false;

            // remember what the previous value was
            $errors = libxml_use_internal_errors(true);
            while ($reader->read()) {
                // check for errors on each read operation
                if ($error = $this->getXmlError()) {
                    throw new ReadException($error);
                }

                // stop if we found our node
                if (($reader->nodeType === \XMLReader::ELEMENT) && (mb_strtolower($reader->name) === $nodeName)) {
                    $found = true;
                    break;
                }
            }

            // node not found, could be an error at the start
            if ($error = $this->getXmlError()) {
                throw new ReadException($error);
            }

            // set the previous value
            libxml_use_internal_errors($errors);

            return $found;
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
        $this->reader->open($resource->getFile()->getPathname(), 'UTF-8', LIBXML_NOENT | LIBXML_PARSEHUGE | LIBXML_NOERROR | LIBXML_NOWARNING);

        $this->key = -1;
        $this->doNext();
    }

    protected function serialize($data)
    {
        return new ParameterBag((array) $this->serializer->decode($data, 'xml'));
    }

    protected function getXmlError()
    {
        $errors = libxml_get_errors();
        libxml_clear_errors();

        // just return the first error
        foreach ($errors as $error) {
            return sprintf('[%s %s] %s (in %s - line %d, column %d)',
                LIBXML_ERR_WARNING === $error->level ? 'WARNING' : 'ERROR',
                $error->code,
                trim($error->message),
                $error->file ? $error->file : 'n/a',
                $error->line,
                $error->column
            );
        }
    }
}
