<?php

namespace Soap4me;

use PHPUnit\Framework\TestCase;
use Soap4me\Exception\QualityException;
use InterNations\Component\HttpMock\PHPUnit\HttpMockTrait;

class EpisodeTest extends TestCase
{
    use HttpMockTrait;

    private $episode;

    /**
     * @param $object - instance in which protected value is being modified
     * @param $property - property on instance being modified
     * @param $value - new value of the property being modified
     *
     * @return void
     * @throws \ReflectionException
     * @todo move to bootstrap
     *
     * Sets a protected property on a given object via reflection
     *
     */
    public function setProtectedProperty($object, $property, $value)
    {
        $reflection = new \ReflectionClass($object);
        $reflection_property = $reflection->getProperty($property);
        $reflection_property->setAccessible(true);
        $reflection_property->setValue($object, $value);
    }

    public static function setUpBeforeClass(): void
    {
        static::setUpHttpMockBeforeClass('8082', 'localhost');
    }

    public static function tearDownAfterClass(): void
    {
        static::tearDownHttpMockAfterClass();
    }

    protected function setUp(): void
    {
        $this->setUpHttpMock();

        // @todo move to bootstrap
        $_ENV['DOWNLOAD_DIR'] = '/';

        $this->episode = new Episode(
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
    }

    protected function tearDown(): void
    {
        $this->tearDownHttpMock();

        $this->episode = null;
    }

    public function testGetEpisodePath()
    {
        $this->assertEquals(
            '/The Simpsons/Season 31/01 The Winter of Our Monetized Content.mp4',
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
            'The Winter of Our Monetized Content',
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
        $testHost = 'http://localhost:8082';

        // @todo move values to constant
        $this->setProtectedProperty($this->episode, 'baseUrl', $testHost);

        $_ENV['COOKIE_FILE'] = 'test_cookie.json';

        $this->http->mock
            ->when()
            ->methodIs('POST')
            ->pathIs('/callback/')
            ->then()
            ->body(json_encode(['ok' => true]))
            ->end();

        $this->http->setUp();

        $this->assertTrue($this->episode->markAsWatched());

        $this->assertSame(
            'POST',
            $this->http->requests->latest()->getMethod()
        );

        $this->assertSame(
            $testHost . '/callback/',
            $this->http->requests->latest()->getUrl()
        );

        $params = [
            'eid' => '12345',
            'token' => 'token-poken',
            'what' => 'mark_watched'
        ];

        $this->assertSame(
            $params,
            $this->http->requests->latest()->getPostFields()->toArray()
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
        $testHost = 'http://localhost:8082';

        $this->setProtectedProperty($this->episode, 'baseUrl', $testHost);

        $_ENV['COOKIE_FILE'] = 'test_cookie.json';

        $this->http->mock
            ->when()
            ->methodIs('POST')
            ->pathIs('/callback/')
            ->then()
            ->body(json_encode(['server' => '666']))
            ->end();

        $this->http->setUp();

        $this->assertSame(
            'https://666.soap4.me/token-poken/12345/7e430f0deb1f56a6d6f140ae82659f1f/',
            $this->episode->getUrl()
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
                ],
            ],
            [
                'current' => 'HD',
                'testing' => [
                    'SD' => true,
                    'HD' => false,
                    'fullHD' => false,
                ],
            ],
            [
                'current' => 'fullHD',
                'testing' => [
                    'SD' => true,
                    'HD' => true,
                    'fullHD' => false,
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
                'quality' => '4k',
                'exception' => QualityException::class,
            ],
        ];
    }
}
