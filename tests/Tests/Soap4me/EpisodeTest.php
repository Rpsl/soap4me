<?php declare(strict_types=1);

namespace Tests\Soap4me;

use Soap4me\Episode;
use Soap4me\Exception\CurlException;
use Soap4me\Exception\QualityException;

use Exception;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class EpisodeTest extends TestCase
{
    private Episode $episode;

    /**
     * @throws QualityException
     */
    final public function setUp(): void
    {
        $this->episode = new Episode(
            'The Simpsons',
            'The Winter of Our Monetized Content?',
            31,
            1,
            'fullHD',
            'ru',
            'some-hash-string',
            12345,
            6789,
            'token-poken'
        );
    }

    final public function tearDown(): void
    {
        unset($this->episode);
    }

    final public function testGetEpisodePath(): void
    {
        TestCase::assertEquals(
            '/The Simpsons/Season 31/s31e01 The Winter of Our Monetized Content.mp4',
            $this->episode->getEpisodePath()
        );
    }

    final public function testGetSeasonPath(): void
    {
        TestCase::assertEquals(
            '/The Simpsons/Season 31',
            $this->episode->getSeasonPath()
        );
    }

    final public function testGetShow(): void
    {
        TestCase::assertEquals(
            'The Simpsons',
            $this->episode->getShow()
        );
    }

    final public function testGetTitle(): void
    {
        TestCase::assertEquals(
            'The Winter of Our Monetized Content?',
            $this->episode->getTitle()
        );
    }

    final public function testGetQuality(): void
    {
        TestCase::assertEquals(
            'fullHD',
            $this->episode->getQuality()
        );
    }

    final public function testGetNumber(): void
    {
        TestCase::assertSame(
            1,
            $this->episode->getNumber()
        );
    }

    final public function testMarkAsWatched(): void
    {
        $container = [];
        $history = Middleware::history($container);

        $mock = new MockHandler([
            new Response(200, [], (string) json_encode(['ok' => true])),
        ]);

        $handler = HandlerStack::create($mock);
        $handler->push($history);

        $client = new Client(['handler' => $handler]);

        $this->episode->setHttpClient($client);

        try {
            TestCase::assertTrue($this->episode->markAsWatched());
        } catch (CurlException $e) {
            TestCase::fail();
        }

        // Only one http request
        TestCase::assertSame(1, count($container));

        /** @var Request $request */
        $request = $container[0]['request'];

        TestCase::assertSame(
            'POST',
            $request->getMethod()
        );

        TestCase::assertSame(
            '/callback/',
            $request->getUri()->getPath()
        );

        $params = [
            'eid' => '12345',
            'token' => 'token-poken',
            'what' => 'mark_watched',
        ];

        parse_str($request->getBody()->getContents(), $post);

        TestCase::assertSame(
            $params,
            $post
        );
    }

    final public function testGetSeason(): void
    {
        TestCase::assertSame(
            31,
            $this->episode->getSeason()
        );
    }

    final public function testGetUrl(): void
    {
        $container = [];
        $history = Middleware::history($container);

        $mock = new MockHandler([
            new Response(200, [], (string) json_encode(['server' => '666'])),
        ]);

        $handler = HandlerStack::create($mock);
        $handler->push($history);

        $client = new Client(['handler' => $handler]);

        $this->episode->setHttpClient($client);

        try {
            $url = $this->episode->getUrl();
        } catch (CurlException $e) {
            TestCase::fail();
        }

        // Only one http request
        TestCase::assertSame(1, count($container));

        TestCase::assertSame(
            'https://666.soap4youand.me/token-poken/12345/7e430f0deb1f56a6d6f140ae82659f1f/',
            $url
        );
    }

    /**
     * @dataProvider isBetterQualityThenProvider
     */
    final public function testIsBetterQualityThen(string $current, array $expectedResults): void
    {
        try {
            $this->episode->setQuality($current);

            foreach ($expectedResults as $testing => $expected) {
                TestCase::assertEquals(
                    $expected,
                    $this->episode->isBetterQualityThen($testing),
                    sprintf('Failed check to better quality. Episode with quality "%s" testing with "%s"', $current,
                        $testing)
                );
            }
        } catch (Exception $e) {
            TestCase::fail(sprintf('unexpected exception "%s"', $e->getMessage()));
        }
    }

    /**
     * @dataProvider setQualityDataProvider
     * @throws QualityException
     */
    final public function testSetQuality(string $quality, ?string $exception): void
    {
        if (!is_null($exception)) {
            TestCase::expectException($exception);
        }

        $this->episode->setQuality($quality);
        TestCase::assertTrue(true);
    }

    final public function isBetterQualityThenProvider(): array
    {
        return [
            [
                'current' => 'SD',
                'testing' => [
                    'SD' => false,
                    'HD' => false,
                    'fullHD' => false,
                    '4k UHD' => false,
                ],
            ],
            [
                'current' => 'HD',
                'testing' => [
                    'SD' => true,
                    'HD' => false,
                    'fullHD' => false,
                    '4k UHD' => false,
                ],
            ],
            [
                'current' => 'fullHD',
                'testing' => [
                    'SD' => true,
                    'HD' => true,
                    'fullHD' => false,
                    '4k UHD' => false,
                ],
            ],
            [
                'current' => '4k UHD',
                'testing' => [
                    'SD' => true,
                    'HD' => true,
                    'fullHD' => true,
                    '4k UHD' => false,
                ],
            ],
        ];
    }

    final public function setQualityDataProvider(): array
    {
        return [
            [
                'quality' => 'fullHD',
                'exception' => null,
            ],
            [
                'quality' => 'HD',
                'exception' => null,
            ],
            [
                'quality' => 'SD',
                'exception' => null,
            ],
            [
                'quality' => '4k UHD',
                'exception' => null,
            ],
            [
                'quality' => '4k',
                'exception' => QualityException::class,
            ],
        ];
    }
}
