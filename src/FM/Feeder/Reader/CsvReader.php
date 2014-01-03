<?php

namespace FM\Feeder\Reader;

use SplFileObject;
use Symfony\Component\HttpFoundation\ParameterBag;
use FM\Feeder\Resource\Resource;

class CsvReader extends AbstractReader
{
    /**
     * @var SplFileObject
     */
    protected $fileObject;

    /**
     * @var array
     */
    protected $mapping;

    /**
     * @var boolean
     */
    protected $useFirstRow;

    /**
     * @var string
     */
    protected $delimiter = ',';

    /**
     * @var string
     */
    protected $enclosure = '"';

    /**
     * @var string
     */
    protected $escape = '\\';

    /**
     * @param array $mapping
     */
    public function setFieldMapping(array $mapping)
    {
        $this->mapping = $mapping;
    }

    /**
     * @param boolean $bool
     */
    public function useFirstRow($bool = true)
    {
        $this->useFirstRow = (boolean) $bool;
    }

    /**
     * @param string $delimiter
     */
    public function setDelimiter($delimiter = ',')
    {
        $this->delimiter = $delimiter;
    }

    /**
     * @param string $enclosure
     */
    public function setEnclosure($enclosure = '"')
    {
        $this->enclosure = $enclosure;
    }

    /**
     * @param string $escape
     */
    public function setEscape($escape = '\\')
    {
        $this->escape = $escape;
    }

    /**
     * @return integer
     */
    public function getRowNumber()
    {
        return $this->key() + 1;
    }

    protected function serialize($data)
    {
        // convert data keys if a mapping is given
        if ($this->mapping) {
            $item = [];
            foreach ($this->mapping as $index => $field) {
                $value = array_key_exists($index, $data) ? $data[$index] : null;
                $item[$field] = $value;
            }

            $data = $item;
        }

        return new ParameterBag($data);
    }

    protected function doKey()
    {
        return $this->fileObject->key();
    }

    protected function doCurrent()
    {
        return $this->fileObject->current();
    }

    protected function doNext()
    {
        $this->fileObject->next();
    }

    protected function doValid()
    {
        return $this->fileObject->valid();
    }

    protected function doRewind()
    {
        $this->fileObject->rewind();
    }

    protected function createReader(Resource $resource)
    {
        $this->fileObject = new SplFileObject($resource->getFile()->getPathname());
        $this->fileObject->setFlags(SplFileObject::READ_CSV | SplFileObject::DROP_NEW_LINE | SplFileObject::SKIP_EMPTY);
        $this->fileObject->setCsvControl($this->delimiter, $this->enclosure, $this->escape);

        if ($this->useFirstRow) {
            $this->setFieldMapping($this->fileObject->current());
            $this->fileObject->next();
        }
    }
}
