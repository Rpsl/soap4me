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
use Soap4me\Quality;

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
            Quality::NewQuality('fullHD'),
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
            $this->episode->getQuality()->getQualityName()
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
            new Response(200, [], (string)json_encode(['server' => '666'])),
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
}
