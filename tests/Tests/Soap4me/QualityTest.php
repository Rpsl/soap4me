<?php declare(strict_types=1);

namespace Tests\Soap4me;

use Exception;
use PHPUnit\Framework\TestCase;
use Soap4me\Exception\QualityException;
use Soap4me\Quality;

class QualityTest extends TestCase
{
    /**
     * @dataProvider isBetterQualityThenProvider
     */
    final public function testIsBetterQualityThen(string $current, array $expectedResults): void
    {
        try {
            $quality = Quality::NewQuality($current);

            foreach ($expectedResults as $testing => $expected) {
                TestCase::assertEquals(
                    $expected,
                    $quality->isBetterQualityThen(Quality::NewQuality($testing)),
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

        Quality::NewQuality($quality);
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