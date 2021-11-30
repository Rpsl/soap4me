<?php declare(strict_types=1);

namespace Tests\Soap4me;

use Soap4me\Episode;
use Soap4me\Exception\QualityException;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class EpisodeTest extends TestCase
{
    private $episode;

    protected function setUp(): void
    {
        // @todo move to bootstrap
        $_ENV['DOWNLOAD_DIR'] = '/';

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

    protected function tearDown(): void
    {
        $this->episode = null;
    }

    public function testGetEpisodePath()
    {
        $this->assertEquals(
            '/The Simpsons/Season 31/s31e01 The Winter of Our Monetized Content.mp4',
            $this->episode->getEpisodePath()
        );
    }

    public function testGetSeasonPath()
    {
        $this->assertEquals(
            '/The Simpsons/Season 31',
            $this->episode->getSeasonPath()
        );
    }

    /**
     * @dataProvider isBetterQualityThenProvider
     */
    public function testIsBetterQualityThen(string $current, array $expectedResults)
    {
        try {
            $this->episode->setQuality($current);

            foreach ($expectedResults as $testing => $expected) {
                $this->assertEquals(
                    $expected,
                    $this->episode->isBetterQualityThen($testing),
                    sprintf('Failed check to better quality. Episode with quality "%s" testing with "%s"', $current,
                        $testing)
                );
            }
        } catch (\Exception $e) {
            $this->fail(sprintf('unexpected exception "%s"', $e->getMessage()));
        }
    }

    public function testGetShow()
    {
        $this->assertEquals(
            'The Simpsons',
            $this->episode->getShow()
        );
    }

    public function testGetTitle()
    {
        $this->assertEquals(
            'The Winter of Our Monetized Content?',
            $this->episode->getTitle()
        );
    }

    public function testGetQuality()
    {
        $this->assertEquals(
            'fullHD',
            $this->episode->getQuality()
        );
    }

    /**
     * @dataProvider setQualityDataProvider
     */
    public function testSetQuality(string $quality, ?string $exception)
    {
        if ($exception) {
            $this->expectException($exception);
        }

        $this->episode->setQuality($quality);
        $this->assertTrue(true);

    }

    public function testGetNumber()
    {
        $this->assertSame(
            1,
            $this->episode->getNumber()
        );
    }

    public function testMarkAsWatched()
    {
        $container = [];
        $history = Middleware::history($container);

        $mock = new MockHandler([
            new Response(200, [], json_encode(['ok' => true])),
        ]);

        $handler = HandlerStack::create($mock);
        $handler->push($history);

        $client = new Client(['handler' => $handler]);

        $this->episode->setHttpClient($client);

        $_ENV['COOKIE_FILE'] = 'test_cookie.json';

        $this->assertTrue($this->episode->markAsWatched());

        // Only one http request
        $this->assertSame(1, count($container));

        /** @var Request $request */
        $request = $container[0]['request'];

        $this->assertSame(
            'POST',
            $request->getMethod()
        );

        $this->assertSame(
            '/callback/',
            $request->getUri()->getPath()
        );

        $params = [
            'eid' => '12345',
            'token' => 'token-poken',
            'what' => 'mark_watched'
        ];

        parse_str($request->getBody()->getContents(), $post);

        $this->assertSame(
            $params,
            $post
        );
    }

    public function testGetSeason()
    {
        $this->assertSame(
            31,
            $this->episode->getSeason()
        );
    }

    public function testGetUrl()
    {
        $container = [];
        $history = Middleware::history($container);

        $mock = new MockHandler([
            new Response(200, [], json_encode(['server' => '666'])),
        ]);

        $handler = HandlerStack::create($mock);
        $handler->push($history);

        $client = new Client(['handler' => $handler]);

        $this->episode->setHttpClient($client);

        $_ENV['COOKIE_FILE'] = 'test_cookie.json';

        $url = $this->episode->getUrl();

        // Only one http request
        $this->assertSame(1, count($container) );

        /** @var Request $request */
        $request = $container[0]['request'];

        $this->assertSame(
            'https://666.soap4.me/token-poken/12345/7e430f0deb1f56a6d6f140ae82659f1f/',
            $url
        );
    }

    public function isBetterQualityThenProvider()
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

    public function setQualityDataProvider()
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
