<?php

namespace FM\Feeder\Transport;

class TalentsoftSoapTransport extends AbstractTransport implements ProgressAwareInterface
{
    /**
     * @var \SoapClient
     */
    protected $client;

    /**
     * @var string
     */
    protected $wsdl;

    /**
     * @var string
     */
    protected $operation;

    /**
     * @var array
     */
    protected $params = [];

    /**
     * @param string $wsdl
     * @param string $operation
     * @param array  $params
     *
     * @return $this
     */
    public static function create($wsdl, $operation, array $params = [])
    {
        $transport = new self(new Connection([]));
        $client    = new \SoapClient($wsdl, ['trace' => true, 'cache_wsdl' => WSDL_CACHE_BOTH]);

        $transport->setClient($client);
        $transport->setWsdl($wsdl);
        $transport->setOperation($operation);
        $transport->setParams($params);

        return $transport;
    }

    /**
     * @return \SoapClient
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param \SoapClient $client
     */
    public function setClient(\SoapClient $client)
    {
        $this->client = $client;
    }

    /**
     * @return string
     */
    public function getWsdl()
    {
        return $this->wsdl;
    }

    /**
     * @param string $wsdl
     */
    public function setWsdl($wsdl)
    {
        $this->wsdl = $wsdl;
    }

    /**
     * @return string
     */
    public function getOperation()
    {
        return $this->operation;
    }

    /**
     * @param string $operation
     */
    public function setOperation($operation)
    {
        $this->operation = $operation;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param array $params
     */
    public function setParams(array $params)
    {
        $this->params = $params;
    }

    /**
     * @return \DateTime
     */
    public function getLastModifiedDate()
    {
        //throw new \Exception('Last modified date not yet implemented');
        return new \DateTime('-1 day');
    }

    /**
     * @return int
     */
    public function getSize()
    {
        if ($response = $this->client->__getLastResponse()) {
            return strlen($response);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function needsDownload($destination, \DateTime $maxAge = null)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function doDownload($destination)
    {
        $tmpFile    = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid();
        $returnData = call_user_func_array([$this->client, $this->getOperation()], $this->getParams());

        if (is_string($returnData)) {
            // we got a value returned that we need for the next call
            $content = $returnData;
        } else {
            // we got actual data returned (\stdClass with $data property)
            // convert it to xml so our feedtype-flow still works
            $content = $this->convertResponseToXml($returnData);
        }

        $fh = fopen($tmpFile, 'w+');
        fwrite($fh, $content);
        fclose($fh);

        rename($tmpFile, $destination);
    }

    /**
     * @param \stdClass $response
     *
     * @return string
     */
    protected function convertResponseToXml(\stdClass $response)
    {
        $xml     = new \SimpleXMLElement('<?xml version="1.0"?><vacatures/>');
        $columns = array_map('strtolower', $response->columnList);
        $rows    = [];

        foreach ($response->data as $x => $row) {
            $rows[$x] = array_combine($columns, $row);
        }

        // function call to convert array to xml
        $this->arrayToXml($rows, $xml, 'vacature');

        return $xml->asXML();
    }

    /**
     * @param array             $array
     * @param \SimpleXMLElement $xml
     * @param string            $subItemPrefix
     */
    protected function arrayToXml(array $array, \SimpleXMLElement $xml, $subItemPrefix)
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                if (!is_numeric($key)) {
                    $node = $xml->addChild($key);
                    $this->arrayToXml($value, $node, $subItemPrefix);
                } else {
                    $node = $xml->addChild($subItemPrefix);
                    $node->addChild('portalid', $this->getParams()['portalid']);
                    $this->arrayToXml($value, $node, $subItemPrefix);
                }
            } else {
                $xml->addChild($key, htmlspecialchars($value));
            }
        }
    }
}
