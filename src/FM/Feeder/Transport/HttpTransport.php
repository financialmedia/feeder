<?php

namespace FM\Feeder\Transport;

use Doctrine\Common\Cache\FilesystemCache;
use Guzzle\Http\Client;
use Guzzle\Cache\DoctrineCacheAdapter;
use Guzzle\Http\Exception\RequestException;
use Guzzle\Http\Message\RequestInterface;
use Guzzle\Http\Message\Response;
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
     * @var CachePlugin
     */
    protected $cache;

    /**
     * Response object for the HEAD call, containing the resource's info
     *
     * @var Response
     */
    protected $info;

    /**
     * Whether to use info (size, last modified, etc). Disable when making a
     * large amount of requests, eg when using an API.
     *
     * @var boolean
     */
    protected $useInfo = true;

    /**
     * @inheritdoc
     */
    public function __clone()
    {
        parent::__clone();

        $this->info = null;
    }

    /**
     * @param string      $url
     * @param string|null $user
     * @param string|null $pass
     *
     * @return HttpTransport
     */
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

    /**
     * @inheritdoc
     */
    public function __toString()
    {
        return $this->getUrl();
    }

    /**
     * @param boolean|null $use
     *
     * @return boolean
     */
    public function useInfo($use = null)
    {
        if (!is_null($use)) {
            $this->useInfo = (boolean) $use;

            return null;
        }

        return $this->useInfo;
    }

    /**
     * @return string
     * @throws \LogicException When url is not defined
     */
    public function getUrl()
    {
        if (!isset($this->connection['url'])) {
            throw new \LogicException('No url defined');
        }

        return $this->connection['url'];
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->connection['url'] = $url;
    }

    /**
     * @return string|null
     */
    public function getUser()
    {
        return isset($this->connection['user']) ? $this->connection['user'] : null;
    }

    /**
     * @return string|null
     */
    public function getPass()
    {
        return isset($this->connection['pass']) ? $this->connection['pass'] : null;
    }

    /**
     * @return CachePlugin
     */
    public function getCache()
    {
        if (null === $this->cache) {
            $this->cache = new CachePlugin([
                'storage' => new DefaultCacheStorage(
                    new DoctrineCacheAdapter(new FilesystemCache(sys_get_temp_dir())),
                    'feeder'
                )
            ]);
        }

        return $this->cache;
    }

    /**
     * @param Client $client
     */
    public function setClient(Client $client)
    {
        // Add the cache plugin to the client object
        $client->addSubscriber($this->getCache());

        $this->client = $client;
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @return Response|null
     */
    public function getRequestInfo()
    {
        if (!$this->useInfo()) {
            return null;
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
            return null;
        }

        return $this->info;
    }

    /**
     * @inheritdoc
     */
    public function getLastModifiedDate()
    {
        if (($response = $this->getRequestInfo()) && ($lastModifiedDate = $response->getLastModified())) {
            return new \DateTime($lastModifiedDate);
        }
    }

    /**
     * @return integer|null
     */
    public function getSize()
    {
        if (($response = $this->getRequestInfo()) && ($size = $response->getContentLength())) {
            return $size;
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function purge()
    {
        parent::purge();

        $this->getCache()->purge($this->getUrl());
    }

    /**
     * @param string $method
     *
     * @return RequestInterface
     * @throws \LogicException
     */
    protected function getRequest($method = 'get')
    {
        if (!$this->client) {
            throw new \LogicException('No client set to use for downloading');
        }

        $request = $this->client->$method($this->getUrl());

        if (($user = $this->getUser()) && ($pass = $this->getPass())) {
            $request->setAuth($user, $pass);
        }

        return $request;
    }

    /**
     * @inheritdoc
     */
    protected function doDownload($destination)
    {
        $request = $this->getRequest();

        try {
            $response = $request->send();
        } catch (RequestException $e) {
            throw new TransportException(sprintf('Could not download feed: %s', $e->getMessage()), null, $e);
        }

        if (!$response->isSuccessful()) {
            throw new TransportException('Server responded with code ' . $response->getStatusCode());
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

    /**
     * @inheritdoc
     */
    protected function needsDownload($destination, \DateTime $maxAge = null)
    {
        // let Guzzle handle caching
        return true;
    }
}
