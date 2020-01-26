<?php declare(strict_types=1);

namespace Tests\Soap4me;

use PHPUnit\Framework\TestCase;
use Psr\Log\AbstractLogger;
use Soap4me\Downloader;
use Soap4me\DownloaderTransport\AbstractTransport;
use Soap4me\Episode;

class DownloaderTest extends TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject|Downloader|Downloader */
    private $downloader;

    private $queue;

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

        $this->queue = null;
    }

    protected function tearDown(): void
    {
        $this->queue = null;
    }

    public function testAdd()
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

        $this->assertSame(
            1,
            count($queue)
        );
    }

    public function testAddBatch()
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

        $this->assertSame(
            3,
            count($queue)
        );
    }

    public function testFilter_Sorting()
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
            $this->assertSame($var, $ep->getNumber());
            $var++;
        }
    }
}