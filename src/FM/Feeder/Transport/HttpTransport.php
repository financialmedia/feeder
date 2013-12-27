<?php

namespace FM\Feeder\Transport;

use Doctrine\Common\Cache\FilesystemCache;
use Guzzle\Http\Client;
use Guzzle\Cache\DoctrineCacheAdapter;
use Guzzle\Plugin\Cache\CachePlugin;
use Guzzle\Plugin\Cache\DefaultCacheStorage;
use Guzzle\Http\Exception\BadResponseException;
use FM\Feeder\Exception\TransportException;

class HttpTransport extends AbstractTransport
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * Response object for the HEAD call, containing the resource's info
     *
     * @var \Guzzle\Http\Message\Response
     */
    protected $info;

    /**
     * Whether to use info (size, last modified, etc). Disable when making a
     * large amount of requests, eg when using an API.
     *
     * @var boolean
     */
    protected $useInfo = true;

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

    public function useInfo($use = null)
    {
        if (!is_null($use)) {
            $this->useInfo = (boolean) $use;

            return;
        }

        return $this->useInfo;
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

    public function getUser()
    {
        return isset($this->connection['user']) ? $this->connection['user'] : null;
    }

    public function getPass()
    {
        return isset($this->connection['pass']) ? $this->connection['pass'] : null;
    }

    public function setClient(Client $client)
    {
        $cachePlugin = new CachePlugin(array(
            'storage' => new DefaultCacheStorage(
                new DoctrineCacheAdapter(
                    new FilesystemCache(sys_get_temp_dir())
                )
            )
        ));

        // Add the cache plugin to the client object
        $client->addSubscriber($cachePlugin);

        $this->client = $client;
    }

    public function getClient()
    {
        return $this->client;
    }

    public function getRequestInfo()
    {
        if (!$this->useInfo()) {
            return;
        }

        if (is_null($this->info)) {
            try {
                $request = $this->getRequest('head');
                $response = $request->send();
                $this->info = $response;
            } catch (BadResponseException $e) {
                // HEAD method is probably not supported
                $this->info = $e->getResponse();
            }
        }

        if (!$this->info->isSuccessful()) {
            return;
        }

        return $this->info;
    }

    public function getLastModifiedDate()
    {
        if (($response = $this->getRequestInfo()) && ($lastModifiedDate = $response->getLastModified())) {
            return new \DateTime($lastModifiedDate);
        }
    }

    public function getSize()
    {
        if (($response = $this->getRequestInfo()) && ($size = $response->getContentLength())) {
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

        if (($user = $this->getUser()) && ($pass = $this->getPass())) {
            $request->setAuth($user, $pass);
        }

        return $request;
    }

    protected function doDownload($destination)
    {
        $request = $this->getRequest('get');

        try {
            $response = $request->send();
        } catch (\Guzzle\Http\Exception\RequestException $e) {
            throw new TransportException(sprintf('Could not download feed (%s)', $e->getMessage()), null, $e);
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

    protected function needsDownload($destination, \DateTime $maxAge = null)
    {
        // let Guzzle handle caching
        return true;
    }
}
