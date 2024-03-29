<?php declare(strict_types=1);

namespace Tests\Soap4me;

use PHPUnit\Framework\TestCase;
use Psr\Log\AbstractLogger;
use Soap4me\Downloader;
use Soap4me\DownloaderTransport\AbstractTransport;
use Soap4me\Episode;
use Soap4me\Exception\QualityException;
use Soap4me\Quality;

class DownloaderTest extends TestCase
{
    /** @var Downloader */
    private Downloader $downloader;

    public function setUp(): void
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

    public function tearDown(): void
    {
        $this->downloader->clearQueue();
    }

    /**
     * @throws QualityException
     */
    public function testAdd(): void
    {
        $episode = new Episode(
            'The Simpsons',
            'The Winter of Our Monetized Content',
            31,
            1,
            Quality::NewQuality('fullHD'),
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

    /**
     * @throws QualityException
     */
    public function testAddBatch(): void
    {
        $episodes = [];

        $episodes[] = new Episode(
            'The Simpsons',
            'The Winter of Our Monetized Content',
            31,
            1,
            Quality::NewQuality('fullHD'),
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
            Quality::NewQuality('fullHD'),
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
            Quality::NewQuality('fullHD'),
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

    /**
     * @throws QualityException
     */
    public function testFilter_Sorting(): void
    {
        $episodes = [];

        $episodes[] = new Episode(
            'The Simpsons',
            'The Winter of Our Monetized Content',
            31,
            4,
            Quality::NewQuality('fullHD'),
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
            Quality::NewQuality('fullHD'),
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
            Quality::NewQuality('fullHD'),
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
            Quality::NewQuality('fullHD'),
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

    /**
     * @throws QualityException
     */
    public function testFilter_Sorting_MaxQuality(): void
    {
        $episodes = [];

        $episodes[] = new Episode(
            'The Simpsons',
            'The Winter of Our Monetized Content',
            31,
            4,
            Quality::NewQuality('4k UHD'),
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
            Quality::NewQuality('fullHD'),
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
            Quality::NewQuality('fullHD'),
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
            Quality::NewQuality('fullHD'),
            'ru',
            'some-hash-string',
            12345,
            6789,
            'token-poken'
        );

        $this->downloader->setMaxQuality('fullHD');
        $this->downloader->addBatch($episodes);

        $queue = $this->downloader->getQueue();

        foreach ($queue as $ep) {
            TestCase::assertSame('fullHD', $ep->getQuality()->getQualityName());
        }
    }
}