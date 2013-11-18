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
        try {
            $file = $this->getFilename();
        } catch (TransportException $e) {
            $file = $this->connection['file'];
        }

        return $this->connection['host'] . ':/' . $file;
    }

    public function getHost()
    {
        return $this->connection['host'];
    }

    public function getUser()
    {
        return isset($this->connection['user']) ? $this->connection['user'] : null;
    }

    public function getPass()
    {
        return isset($this->connection['pass']) ? $this->connection['pass'] : null;
    }

    public function getMode()
    {
        return isset($this->connection['mode']) ? $this->connection['mode'] : null;
    }

    public function setMode($mode)
    {
        $this->connection['mode'] = $mode;
    }

    public function getPasv()
    {
        return isset($this->connection['pasv']) ? (boolean) $this->connection['pasv'] : null;
    }

    public function setPasv($pasv)
    {
        $this->connection['pasv'] = (boolean) $pasv;
    }

    public function getPattern()
    {
        return isset($this->connection['pattern']) ? (boolean) $this->connection['pattern'] : null;
    }

    public function setPattern($pattern)
    {
        $this->connection['pattern'] = (boolean) $pattern;
    }

    public function getFtpConnection()
    {
        if (is_null($this->ftpConnection)) {
            $conn = ftp_connect($this->connection['host']);
            if (($conn === false) || (ftp_login($conn, $this->connection['user'], $this->connection['pass']) === false)) {
                throw new TransportException(is_resource($conn) ? 'Could not login to FTP' : 'Could not make FTP connection');
            }

            $this->ftpConnection = $conn;

            if (null !== $pasv = $this->getPasv()) {
                ftp_pasv($this->ftpConnection, $pasv);
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
            $file = $this->connection['file'];
            $pattern = $this->getPattern();

            // see if we need to use a pattern, this can also be the case with a wildcard
            if (!$pattern && (false !== strpos($file, '*'))) {
                $pattern = true;

                list($start, $end) = explode('*', $file);
                $file = '/^' . preg_quote($start, '/') . '.*' . preg_quote($end, '/') . '$/i';
            }

            $this->fileName = $this->searchFile($file, $pattern);
        }

        return $this->fileName;
    }

    public function getLastModifiedDate()
    {
        // see if uploaded feed is newer
        if ($ts = ftp_mdtm($this->getFtpConnection(), $this->getFilename())) {
            return new \DateTime('@' . $ts);
        }
    }

    public function getSize()
    {
        return ftp_size($this->getFtpConnection(), $this->getFilename());
    }

    /**
     * @param  string  $name
     * @param  boolean $pattern
     * @return string
     */
    protected function searchFile($name, $pattern = false)
    {
        $conn = $this->getFtpConnection();
        $files = ftp_nlist($conn, '.');

        if (false === $files) {
            throw new TransportException('Error listing files from directory ".". You might want to try passive mode using "pasv: true" in your transport configuration.');
        }

        // no pattern, search for direct match
        if (!$pattern) {
            if (!in_array($name, $files) && !in_array('./' . $name, $files)) {
                throw new TransportException(sprintf('File "%s" was not found on FTP', $name));
            }

            return $name;
        }

        // use pattern
        foreach ($files as $file) {
            if (preg_match($name, $file)) {
                return $file;
            }
        }

        throw new TransportException(sprintf('Pattern "%s" was not found on FTP', $name));
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

        $mode = $this->getMode() ? constant('FTP_' . strtoupper($this->getMode())) : FTP_ASCII;

        $ret = ftp_nb_get($conn, $tmpFile, $file, $mode);
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
