<?php

namespace FM\Feeder\Transport;

use FM\Feeder\Exception\TransportException;

class SoapTransport extends AbstractTransport implements ProgressAwareInterface
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
        $conn      = new Connection([]);
        $transport = new self($conn);
        $client    = new \SoapClient($wsdl);
        
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
    }

    /**
     * @return int
     */
    public function getSize()
    {
        if ($request = $this->client->__getLastRequest()) {
            return strlen($request);
        }
    }

    /**
     * @param string $destination
     */
    protected function doDownload($destination)
    {
        $tmpFile = $this->downloadToTmpFile();

        // download complete, move to actual destination
        rename($tmpFile, $destination);
    }

    /**
     * @return string
     *
     * @throws TransportException
     */
    protected function downloadToTmpFile()
    {
        $tmpFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid();
        $data    = call_user_func_array([$this->client, $this->operation], $this->params);
        $fh      = fopen($tmpFile, 'w+');

        fwrite($fh, $data);
        fclose($fh);

        return $tmpFile;
    }
}
