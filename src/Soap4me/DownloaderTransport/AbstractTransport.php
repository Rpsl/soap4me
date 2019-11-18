<?php declare(strict_types=1);

namespace Soap4me\DownloaderTransport;

use League\Flysystem\FilesystemInterface;
use Psr\Log\LoggerInterface;
use Soap4me\Episode;

abstract class AbstractTransport
{
    /** @var LoggerInterface */
    protected $logger;

    /** @var FilesystemInterface */
    protected $filesystem;

    public function __construct(LoggerInterface $logger, FilesystemInterface $filesystem)
    {
        $this->logger = $logger;
        $this->filesystem = $filesystem;
    }

    public function download(Episode $episode): bool
    {

    }
}