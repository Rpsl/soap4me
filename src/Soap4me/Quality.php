<?php declare(strict_types=1);

namespace Soap4me;

use Soap4me\Exception\QualityException;

class Quality
{
    private int $currentRank;

    private string $currentName;

    // @todo Enum
    public const QUALITY_FULL_HD = 'fullHD';
    public const QUALITY_HD = 'HD';
    public const QUALITY_SD = 'SD';
    public const QUALITY_4K = '4k UHD';

    /**
     * Quality rank. Bigger is better
     *
     * @var array
     */
    private static array $QUALITY_RANK = [
        self::QUALITY_SD => 1,
        self::QUALITY_HD => 2,
        self::QUALITY_FULL_HD => 3,
        self::QUALITY_4K => 4,
    ];

    public function __construct(string $name)
    {
        $this->currentName = $name;
        $this->currentRank = self::$QUALITY_RANK[$name];
    }

    /**
     * Parse and get Quality object
     *
     * @param string $quality
     *
     * @return Quality
     *
     * @throws QualityException
     */
    static function NewQuality(string $quality): Quality
    {
        if (isset(self::$QUALITY_RANK[$quality])) {
            return new Quality($quality);
        }

        throw new QualityException(sprintf('Unknown quality type :: %s', $quality));

    }

    /**
     * Compare quality of this episode with another
     *
     * @param Quality $quality
     *
     * @return bool
     */
    public function isBetterQualityThen(Quality $quality): bool
    {
        if ($this->getQualityRank() > $quality->getQualityRank()) {
            return true;
        }

        return false;
    }

    public function getQualityRank(): int
    {
        return $this->currentRank;
    }

    public function getQualityName(): string
    {
        return $this->currentName;
    }

}