<?php


namespace Soap4me\DownloaderTransport;

use League\Flysystem\FilesystemInterface;
use Psr\Log\LoggerInterface;
use Soap4me\Episode;

abstract class AbstractTransport
{
    protected $logger;

    /** @var FilesystemInterface */
    protected $filesystem;

    public function __construct(LoggerInterface $logger, FilesystemInterface $filesystem)
    {
        $this->logger = $logger;
        $this->filesystem = $filesystem;
    }

    public function download(Episode $episode)
    {

    }
}