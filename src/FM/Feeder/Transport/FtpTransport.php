<?php

namespace FM\Feeder\Transport;

use FM\Feeder\FeedEvents;
use FM\Feeder\Event\DownloadProgressEvent;
use FM\Feeder\Exception\TransportException;

class FtpTransport extends AbstractTransport
{
    protected $ftpConnection;
    protected $fileName;

    public function __clone()
    {
        parent::__clone();

        $this->ftpConnection = null;
        $this->fileName = null;
    }

    public static function create($host, $user = null, $pass = null, $file, array $options = array())
    {
        $conn = new Connection(array_merge(
            [
                'host' => $host,
                'user' => $user,
                'pass' => $pass,
                'file' => $file
            ],
            $options
        ));
        $transport = new self($conn);

        return $transport;
    }

    public function __toString()
    {
        return $this->connection['host'] . ':/' . $this->connection['file'];
    }

    public function getMode()
    {
        return isset($this->connection['mode']) ? constant('FTP_' . strtoupper($this->connection['mode'])) : FTP_ASCII;
    }

    public function getFtpConnection()
    {
        if (is_null($this->ftpConnection)) {
            $conn = ftp_connect($this->connection['host']);
            if (($conn === false) || (ftp_login($conn, $this->connection['user'], $this->connection['pass']) === false)) {
                throw new TransportException(is_resource($conn) ? 'Could not login to FTP' : 'Could not make FTP connection');
            }

            $this->ftpConnection = $conn;

            $pasv = isset($this->connection['pasv']) ? (bool) $this->connection['pasv'] : null;

            if (null !== $pasv) {
                ftp_pasv($conn, $pasv);
            }
        }

        return $this->ftpConnection;
    }

    public function setFilename($file)
    {
        $this->connection['file'] = $file;
        $this->fileName = null;
    }

    /**
     * Returns the file to download from the ftp. Handles globbing rules and
     * checks if the file is listed in the remote dir.
     *
     * @return string
     * @throws TransportException When remote file could not be found
     */
    public function getFilename()
    {
        if (!$this->fileName) {
            $conn = $this->getFtpConnection();

            $files = ftp_nlist($conn, '.');

            if (false !== $pos = strpos($this->connection['file'], '*')) {

                // wildcard supplied
                list($start, $end) = explode('*', $this->connection['file']);

                $match = false;
                foreach ($files as $file) {
                    if (preg_match('/^' . preg_quote($start, '/') . '.*' . preg_quote($end, '/') . '$/i', $file)) {
                        $this->connection['file'] = $file;
                        $match = true;
                        break;
                    }
                }

                if ($match === false) {
                    throw new TransportException('Globbing, but no files matched');
                }
            }

            if (false === $files) {
                throw new TransportException(sprintf('Error while listing files in directory ".". You might want to try passive mode using "pasv: true" in your feed.transport configuration.', $this->connection['file']));
            }

            if (!in_array($this->connection['file'], $files) && !in_array('./' . $this->connection['file'], $files)) {
                throw new TransportException(sprintf('File "%s" was not found on FTP', $this->connection['file']));
            }

            $this->fileName = $this->connection['file'];
        }

        return $this->fileName;
    }

    public function getLastModifiedDate()
    {
        // see if uploaded feed is newer
        if ($ts = ftp_mdtm($this->getFtpConnection(), $this->connection['file'])) {
            return new \DateTime('@' . $ts);
        }
    }

    public function getSize()
    {
        return ftp_size($this->getFtpConnection(), $this->getFilename());
    }

    protected function doDownload($destination)
    {
        $tmpFile = $this->downloadToTmpFile();

        // download complete, move to actual destination
        rename($tmpFile, $destination);
    }

    protected function downloadToTmpFile()
    {
        $conn = $this->getFtpConnection();
        $file = $this->getFilename();

        $tmpFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . basename($file);
        $fileSize = $this->getSize();

        // TODO: tmp workaround until php 5.5.5 is release with a fix for the current segfault in ftp_nb_continue
        if (!ftp_get($conn, $tmpFile, $file, $this->getMode())) {
            throw new TransportException(sprintf('Error downloading feed to %s', $tmpFile));
        }

        return $tmpFile;

        $ret = ftp_nb_get($conn, $tmpFile, $file, $this->getMode());
        $currentBytes = 0;
        while ($ret === FTP_MOREDATA) {
            $ret = ftp_nb_continue($conn);
            clearstatcache();
            $bytes = filesize($tmpFile);
            $diff = $bytes - $currentBytes;
            $currentBytes = $bytes;

            $this->eventDispatcher->dispatch(FeedEvents::DOWNLOAD_PROGRESS, new DownloadProgressEvent($currentBytes, $diff, $fileSize));
        }

        if ($ret !== FTP_FINISHED) {
            throw new TransportException(sprintf('Error downloading feed to %s', $tmpFile));
        }

        return $tmpFile;
    }
}
