<?php

namespace FM\Feeder\Transport;

use Symfony\Component\EventDispatcher\EventDispatcher;
use FM\Feeder\FeedEvents;
use FM\Feeder\Event\DownloadEvent;
use FM\Feeder\Exception\TransportException;

abstract class AbstractTransport implements Transport
{
    /**
     * There's some logic here that requires only this class has access to it.
     *
     * @var string
     */
    private $destination;

    /**
     * Directory where transport will download to. The file name is generated if
     * this is used.
     *
     * @var string
     */
    protected $destinationDir;

    /**
     * The number of seconds that the transport may be cached
     *
     * @var integer
     */
    protected $maxAge;

    protected $connection;
    protected $downloaded;
    protected $eventDispatcher;

    public function __construct(Connection $conn, $destination = null, EventDispatcher $dispatcher = null)
    {
        $this->connection      = $conn;
        $this->destination     = $destination;
        $this->eventDispatcher = $dispatcher ?: new EventDispatcher();
        $this->downloaded      = false;
        $this->maxAge          = 86400;
    }

    public function __clone()
    {
        $this->destination = false;
        $this->downloaded = false;
        $this->connection = clone $this->connection;
    }

    public function __toString()
    {
        return $this->connection->__toString();
    }

    public function getConnection()
    {
        return $this->connection;
    }

    public function setEventDispatcher(EventDispatcher $dispatcher)
    {
        $this->eventDispatcher = $dispatcher;
    }

    public function getEventDispatcher()
    {
        return $this->eventDispatcher;
    }

    public function setMaxAge($seconds)
    {
        $this->maxAge = $seconds;
    }

    public function getMaxAge()
    {
        return $this->maxAge;
    }

    public function setDestination($destination)
    {
        if ($this->destination) {
            throw new \LogicException(
                'Destination is already set and is immutable. If you want to
                change the destination, you can clone this transport or create
                a new one'
            );
        }

        $this->destination = $destination;
    }

    public function getDestination()
    {
        if (!$this->destination) {
            $this->destination = $this->getDefaultDestination();
        }

        return $this->destination;
    }

    public function setDestinationDir($destinationDir)
    {
        if ($this->destination) {
            throw new \LogicException(
                'Destination is already set and is immutable. If you want to
                change the destination directory, you can clone this transport
                or create a new one'
            );
        }

        $this->destinationDir = $destinationDir;
    }

    public function getDestinationDir()
    {
        return $this->destinationDir ?: sys_get_temp_dir();
    }

    public function getDefaultDestination()
    {
        return sprintf('%s/%s', rtrim($this->getDestinationDir(), '/'), $this->connection->getHash());
    }

    public function getFile()
    {
        $maxAge = new \DateTime();
        $maxAge->sub(new \DateInterval(sprintf('PT%dS', $this->maxAge)));

        return new \SplFileObject($this->download($maxAge));
    }

    final public function download(\DateTime $maxAge = null)
    {
        $destination = $this->getDestination();

        if ($this->downloaded == false) {
            $event = new DownloadEvent($this);

            // check if we need to download feed
            if ($this->needsDownload($destination, $maxAge)) {
                // make sure directory exists
                $dir = dirname($destination);
                if (!is_dir($dir)) {
                    if (true !== @mkdir($dir, 0777, true)) {
                        throw new TransportException(sprintf('Could not create feed dir "%s"', $dir));
                    }
                }

                $this->eventDispatcher->dispatch(FeedEvents::PRE_DOWNLOAD, $event);
                $this->doDownload($destination);
                $this->eventDispatcher->dispatch(FeedEvents::POST_DOWNLOAD, $event);
            } else {
                $this->eventDispatcher->dispatch(FeedEvents::CACHED, $event);
            }

            $this->downloaded = true;
        }

        return $destination;
    }

    final public static function getDefaultUserAgent()
    {
        return 'Feeder/1.0';
    }

    protected function needsDownload($destination, \DateTime $maxAge = null)
    {
        // download if file does not exist
        if (!file_exists($destination)) {
            return true;
        }

        // if file exists and no max-age is given, use the cached file
        if (!$maxAge instanceof \DateTime) {
            return false;
        }

        // download if ttl is passed
        $mtime = new \Datetime('@' . filemtime($destination));

        // see if cache has expired
        if ($mtime < $maxAge) {
            return true;
        }

        // check with last modified date (if available)
        if (($lastMod = $this->getLastModifiedDate()) && ($mtime < $lastMod)) {
            return true;
        }

        // all checks passed, use the cached version
        return false;
    }

    abstract protected function doDownload($destination);
}
