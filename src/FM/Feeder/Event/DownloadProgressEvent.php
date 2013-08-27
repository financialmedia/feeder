<?php

namespace FM\Feeder\Event;

use Symfony\Component\EventDispatcher\Event;

class DownloadProgressEvent extends Event
{
    protected $bytesDownloaded;
    protected $bytesWritten;
    protected $total;

    public function __construct($bytesDownloaded, $bytesWritten, $total)
    {
        $this->bytesDownloaded = $bytesDownloaded;
        $this->bytesWritten = $bytesWritten;
        $this->total = $total;
    }

    /**
     * Returns the total number of bytes downloaded so far.
     *
     * @return integer
     */
    public function getBytesDownloaded()
    {
        return $this->bytesDownloaded;
    }

    /**
     * Returns the number of bytes written for this event. Useful for
     * determining the relative progress (or download speed)
     *
     * @return integer
     */
    public function getBytesWritten()
    {
        return $this->bytesWritten;
    }

    /**
     * Returns the total number of bytes to be downloaded
     *
     * @return integer
     */
    public function getTotal()
    {
        return $this->total;
    }
}
