<?php

namespace FM\Feeder\Reader;

use FM\Feeder\Exception\ReadException;
use FM\Feeder\Resource\Resource;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\CustomNormalizer;
use Symfony\Component\Serializer\Serializer;

class XmlReader extends AbstractReader
{
    /**
     * @var \XMLReader
     */
    protected $reader;

    /**
     * @var \Closure
     */
    protected $nextNode;

    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * @var integer
     */
    protected $key;

    public function __construct($resources = null, EventDispatcherInterface $dispatcher = null)
    {
        parent::__construct($resources, $dispatcher);

        $this->serializer = new Serializer(
            array(new CustomNormalizer()),
            array('xml' => new XmlEncoder())
        );
    }

    /**
     * @param  mixed                     $nextNode Callback to get the next node from the current resource. Can be a callback or a node name.
     * @return \Closure
     * @throws \InvalidArgumentException
     */
    public function setNodeCallback($nextNode)
    {
        if ($nextNode instanceof \Closure) {
            return $this->nextNode = $nextNode;
        }

        if (!is_string($nextNode)) {
            throw new \InvalidArgumentException('Expecting a string of callback for nextNode');
        }

        $nodeName = mb_strtolower($nextNode);

        return $this->nextNode = function (\XMLReader $reader) use ($nodeName) {
            while ($this->readerOperation($reader, 'read')) {
                // stop if we found our node
                if (($reader->nodeType === \XMLReader::ELEMENT) && (mb_strtolower($reader->name) === $nodeName)) {
                    return true;
                }
            }

            return false;
        };
    }

    protected function doKey()
    {
        return $this->key;
    }

    protected function doCurrent()
    {
        return $this->readerOperation($this->reader, 'readOuterXml');
    }

    protected function doNext()
    {
        $this->moveToNextNode($this->reader);
    }

    protected function doRewind()
    {
        $this->reader->close();
        $this->open($this->resource->getFile()->getPathname());

        $this->key = -1;

        $this->next();
    }

    protected function doValid()
    {
        return (boolean) $this->doCurrent();
    }

    protected function moveToNextNode(\XMLReader $reader)
    {
        if (!$this->nextNode instanceof \Closure) {
            throw new \LogicException('No callback set to get next node');
        }

        $this->key++;

        return call_user_func($this->nextNode, $reader);
    }

    protected function createReader(Resource $resource)
    {
        $this->reader = new \XmlReader();
        $this->open($resource->getFile()->getPathname());

        $this->key = -1;
        $this->next();
    }

    protected function serialize($data)
    {
        return new ParameterBag((array) $this->serializer->decode($data, 'xml'));
    }

    protected function open($file, $options = null)
    {
        if (is_null($options)) {
            $options = LIBXML_NOENT | LIBXML_NONET | LIBXML_COMPACT | LIBXML_PARSEHUGE | LIBXML_NOERROR | LIBXML_NOWARNING;
        }

        $this->reader->open($file, null, $options);
    }

    private function getXmlError()
    {
        // just return the first error
        if ($error = libxml_get_last_error()) {
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

    private function readerOperation(\XmlReader $reader, $method)
    {
        // clear any previous errors
        libxml_clear_errors();

        // remember current settings
        $errors = libxml_use_internal_errors(true);
        $entities = libxml_disable_entity_loader(true);

        // perform the operation
        $retval = $reader->$method();

        // get the last error, if any
        $error = $this->getXmlError();

        // reset everything, clear the error buffer again
        libxml_clear_errors();
        libxml_use_internal_errors($errors);
        libxml_disable_entity_loader($entities);

        if ($error) {
            throw new ReadException($error);
        }

        return $retval;
    }
}
