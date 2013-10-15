<?php

namespace FM\Feeder\Transport;

use Guzzle\Http\Client;
use FM\Feeder\Exception\TransportException;

class HttpTransport extends AbstractTransport
{
    protected $client;
    protected $info;

    public function __clone()
    {
        parent::__clone();

        $this->info = null;
    }

    public static function create($url, $user = null, $pass = null)
    {
        $conn = new Connection([
            'url'  => $url,
            'user' => $user,
            'pass' => $pass,
        ]);
        $transport = new self($conn);

        $client = new Client();
        $client->setUserAgent(static::getDefaultUserAgent());
        $transport->setClient($client);

        return $transport;
    }

    public function __toString()
    {
        return $this->getUrl();
    }

    public function getUrl()
    {
        if (!isset($this->connection['url'])) {
            throw new \LogicException('No url defined');
        }

        return $this->connection['url'];
    }

    public function setUrl($url)
    {
        $this->connection['url'] = $url;
    }

    public function setClient(Client $client)
    {
        $this->client = $client;
    }

    public function getClient()
    {
        return $this->client;
    }

    public function getRequestInfo()
    {
        if (!$this->info) {
            $request = $this->getRequest('head');
            $response = $request->send();
            $this->info = $response;
        }

        return $this->info;
    }

    public function getLastModifiedDate()
    {
        $response = $this->getRequestInfo();
        if ($lastModifiedDate = (string) $response->getHeader('Last-Modified')) {
            return new \DateTime($lastModifiedDate);
        }
    }

    public function getSize()
    {
        $response = $this->getRequestInfo();
        if ($size = (string) $response->getHeader('Content-Length')) {
            return $size;
        }
    }

    protected function getRequest($method)
    {
        if (!$this->client) {
            throw new \LogicException('No client set to use for downloading');
        }

        // perform HEAD request
        $request = $this->client->$method($this->getUrl(), array('User-Agent' => 'Feeder/1.0'));

        if (isset($this->connection['user']) && isset($this->connection['pass'])) {
            $request->setAuth($this->connection['user'], $this->connection['pass']);
        }

        return $request;
    }

    protected function doDownload($destination)
    {
        $request = $this->getRequest('get');

        try {
            $response = $request->send();
        } catch (\Guzzle\Http\Exception\RequestException $e) {
            throw new TransportException('Could not download feed', null, $e);
        }

        if (!$response->isSuccessful()) {
            throw new TransportException('Server responded with code ' . $this->response->getStatusCode());
        }

        // get body as a stream, this way we consume less memory
        $stream = $response->getBody();
        $stream->rewind();

        // save to destination
        $f = fopen($destination, 'w');
        while (!$stream->feof()) {
            fwrite($f, $stream->read(1024));
        }

        fclose($f);
    }
}
