<?php declare(strict_types=1);

namespace Soap4me\Notify;

use Psr\Log\LoggerInterface;
use Soap4me\Episode;

abstract class AbstractNotify
{
    /** @var LoggerInterface */
    protected $logger;

    /** @var array */
    protected $config;

    public function __construct(LoggerInterface $logger, array $config)
    {
        $this->logger = $logger;
        $this->config = $config;
    }

    public function notify(Episode $episode)
    {

    }
}