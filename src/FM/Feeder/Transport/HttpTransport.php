<?php

namespace FM\Feeder\Transport;

use FM\Feeder\Event\DownloadProgressEvent;
use FM\Feeder\Exception\TransportException;
use FM\Feeder\FeedEvents;
use Guzzle\Common\Event;
use Guzzle\Http\Client;
use Guzzle\Http\Exception\BadResponseException;
use Guzzle\Http\Exception\RequestException;
use Guzzle\Http\Message\RequestInterface;
use Guzzle\Http\Message\Response;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class HttpTransport extends AbstractTransport implements EventSubscriberInterface
{
    /**
     * @var Client
     */
    protected $client;

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
     * Track download progress
     *
     * @var int number of bytes downloaded in total
     */
    protected $lastDownloaded = 0;

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
     * @param Client $client
     */
    public function setClient(Client $client)
    {
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

        return null;
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
     * @param \Guzzle\Common\Event $event
     */
    public function onCurlProgress(Event $event)
    {
        if ($event['handle'] && $event['downloaded']) {
            $this->eventDispatcher->dispatch(
                FeedEvents::DOWNLOAD_PROGRESS,
                new DownloadProgressEvent($event['downloaded'], $event['downloaded'] - $this->lastDownloaded, $event['download_size'])
            );

            $this->lastDownloaded = $event['downloaded'];
        }
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'curl.callback.progress' => 'onCurlProgress',
        ];
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

        /** @var RequestInterface $request */
        $request = $this->client->$method($this->getUrl());

        if (($user = $this->getUser()) && ($pass = $this->getPass())) {
            $request->setAuth($user, $pass);
        }

        if ('get' === $method) {
            // enable progress tracking
            $request->addSubscriber($this);
            $request->getCurlOptions()->set('progress', true);
        }

        return $request;
    }

    /**
     * @inheritdoc
     */
    protected function doDownload($destination)
    {
        $f = fopen($destination, 'w');

        $request = $this->getRequest();
        $request->setResponseBody($f);

        try {
            $this->lastDownloaded = 0;

            $response = $request->send();
        } catch (RequestException $e) {
            throw new TransportException(sprintf('Could not download feed: %s', $e->getMessage()), null, $e);
        }

        if (!$response->isSuccessful()) {
            throw new TransportException('Server responded with code ' . $response->getStatusCode());
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
