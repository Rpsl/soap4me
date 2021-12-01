<?php declare(strict_types=1);

namespace Tests\Soap4me;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\AbstractLogger;
use Soap4me\Downloader;
use Soap4me\DownloaderTransport\AbstractTransport;
use Soap4me\Episode;

class DownloaderTest extends TestCase
{
    /** @var Downloader */
    private Downloader $downloader;

    protected function setUp(): void
    {
        /**
         * @var AbstractLogger $logger
         */
        $logger = $this->getMockBuilder(AbstractLogger::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        /**
         * @var AbstractTransport $transport
         */
        $transport = $this->getMockBuilder(AbstractTransport::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->downloader = new Downloader($logger, $transport);

        $this->downloader->clearQueue();
    }

    protected function tearDown(): void
    {
        $this->downloader->clearQueue();
    }

    public function testAdd(): void
    {
        $episode = new Episode(
            'The Simpsons',
            'The Winter of Our Monetized Content',
            31,
            1,
            'fullHD',
            'ru',
            'some-hash-string',
            12345,
            6789,
            'token-poken'
        );

        $this->downloader->add($episode);

        $queue = $this->downloader->getQueue();

        TestCase::assertSame(
            1,
            count($queue)
        );
    }

    public function testAddBatch(): void
    {
        $episodes = [];

        $episodes[] = new Episode(
            'The Simpsons',
            'The Winter of Our Monetized Content',
            31,
            1,
            'fullHD',
            'ru',
            'some-hash-string',
            12345,
            6789,
            'token-poken'
        );

        $episodes[] = new Episode(
            'The Simpsons',
            'The Winter of Our Monetized Content',
            31,
            2,
            'fullHD',
            'ru',
            'some-hash-string',
            12345,
            6789,
            'token-poken'
        );

        $episodes[] = new Episode(
            'The Simpsons',
            'The Winter of Our Monetized Content',
            31,
            3,
            'fullHD',
            'ru',
            'some-hash-string',
            12345,
            6789,
            'token-poken'
        );

        $this->downloader->addBatch($episodes);

        $queue = $this->downloader->getQueue();

        TestCase::assertSame(
            3,
            count($queue)
        );
    }

    public function testFilter_Sorting(): void
    {
        $episodes = [];

        $episodes[] = new Episode(
            'The Simpsons',
            'The Winter of Our Monetized Content',
            31,
            4,
            'fullHD',
            'ru',
            'some-hash-string',
            12345,
            6789,
            'token-poken'
        );

        $episodes[] = new Episode(
            'The Simpsons',
            'The Winter of Our Monetized Content',
            31,
            2,
            'fullHD',
            'ru',
            'some-hash-string',
            12345,
            6789,
            'token-poken'
        );

        $episodes[] = new Episode(
            'The Simpsons',
            'The Winter of Our Monetized Content',
            31,
            3,
            'fullHD',
            'ru',
            'some-hash-string',
            12345,
            6789,
            'token-poken'
        );

        $episodes[] = new Episode(
            'The Simpsons',
            'The Winter of Our Monetized Content',
            31,
            1,
            'fullHD',
            'ru',
            'some-hash-string',
            12345,
            6789,
            'token-poken'
        );

        $this->downloader->addBatch($episodes);

        $queue = $this->downloader->getQueue();

        $var = 1;

        foreach ($queue as $ep) {
            TestCase::assertSame($var, $ep->getNumber());
            $var++;
        }
    }
}