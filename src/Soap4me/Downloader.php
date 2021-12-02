<?php declare(strict_types=1);

namespace Soap4me;

use Psr\Log\LoggerInterface;
use Soap4me\DownloaderTransport\AbstractTransport;
use Soap4me\Exception\CurlException;
use Soap4me\Notify\AbstractNotify;

class Downloader
{
    /** @var LoggerInterface */
    private $logger;

    /** @var AbstractTransport */
    private $transport;

    /** @var Episode[] */
    private $queue = [];

    /** @var AbstractNotify|null */
    private $notify;

    public function __construct(LoggerInterface $logger, AbstractTransport $transport)
    {
        $this->logger = $logger;
        $this->transport = $transport;
    }

    /**
     * @param AbstractNotify $notify
     *
     * @return $this
     */
    public function setNotify(AbstractNotify $notify)
    {
        $this->notify = $notify;

        return $this;
    }

    /**
     * @param Episode[] $episodes
     *
     * @return Downloader
     */
    public function addBatch($episodes = [])
    {
        array_map(function ($v): void {
            $this->add($v);
        }, $episodes);

        return $this;
    }

    /**
     * @param Episode $episode
     */
    public function add(Episode $episode): void
    {
        $this->logger->info(sprintf(
            "Add episode %s - S%02dE%02d (%s) %s",
            $episode->getShow(),
            $episode->getSeason(),
            $episode->getNumber(),
            $episode->getQuality(),
            $episode->getTitle()
        ));

        $this->queue[] = $episode;
    }

    public function download(): void
    {
        $this->filter();

        array_walk($this->queue, function ($v): void {
            /** @var Episode $v */
            $this->transport->download($v);

            try {
                $v->markAsWatched();
            } catch (CurlException $e) {
                $this->logger->error($e->getMessage());
            }

            if (!is_null($this->notify)) {
                $this->notify->notify($v);
            }
        });
    }

    /**
     * Return filterd queue
     *
     * @return Episode[]
     */
    public function getQueue()
    {
        $this->filter();

        return $this->queue;
    }

    /**
     * Clear queue
     */
    public function clearQueue(): void
    {
        $this->queue = [];
    }

    /**
     * Filter Queue. Leavel only best quality episodes
     */
    private function filter(): void
    {
        $tmpQueue = [];

        foreach ($this->queue as $v) {
            if (!isset($tmpQueue[$v->getShow()][$v->getSeason()][$v->getNumber()])) {
                $tmpQueue[$v->getShow()][$v->getSeason()][$v->getNumber()] = $v;
            } else {
                /** @var Episode $existing */
                $existing = $tmpQueue[$v->getShow()][$v->getSeason()][$v->getNumber()];

                if ($existing->isBetterQualityThen($v->getQuality())) {
                    continue;
                }

                $tmpQueue[$v->getShow()][$v->getSeason()][$v->getNumber()] = $v;

                $this->logger->debug(sprintf(
                    "Episode %s - S%02dE%02d %s with quality %s replaced by quality %s",
                    $v->getShow(),
                    $v->getSeason(),
                    $v->getNumber(),
                    $v->getTitle(),
                    $existing->getQuality(),
                    $v->getQuality()
                ));
            }

        }

        $this->queue = [];

        // @todo look as shit, but we have only one episode after filtering
        foreach ($tmpQueue as $show) {
            ksort($show);
            foreach ($show as $season) {
                $season = array_reverse($season, true);
                ksort($season);
                foreach ($season as $episode) {
                    $this->add($episode);
                }
            }
        }
    }
}