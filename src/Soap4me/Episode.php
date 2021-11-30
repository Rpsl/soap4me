<?php declare(strict_types=1);

namespace Soap4me;

use Soap4me\Exception\CurlException;
use Soap4me\Exception\QualityException;

/**
 * @property-read string $baseUrl
 */
class Episode
{
    use CurlTrait;

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
    private static $QUALITY_RANK = [
        self::QUALITY_SD => 1,
        self::QUALITY_HD => 2,
        self::QUALITY_FULL_HD => 3,
        self::QUALITY_4K => 4,
    ];

    /** @var string $show TV Show name */
    private $show;

    /** @var string $title TV Show episode title */
    private $title;

    /** @var int $season TV Show season number */
    private $season;

    /** @var int $number TV Show episode number */
    private $number;

    /** @var string $quality TV Show episode quality */
    private $quality;

    /** @var string $translate TV Show sound translate */
    private $translate;

    /** @var string $hash of downloaded file */
    private $hash;

    /** @var int $eid of downloaded file */
    private $eid;

    /** @var int $sid of downloaded file */
    private $sid;

    /** @var string $token for video player */
    private $token;

    /**
     * Episode constructor.
     *
     * @param string $show
     * @param string $title
     * @param int $season
     * @param int $number
     * @param string $quality
     * @param string $translate
     * @param string $hash
     * @param int $eid
     * @param int $sid
     * @param string $token
     *
     * @throws QualityException
     */
    public function __construct(
        string $show,
        string $title,
        int $season,
        int $number,
        string $quality,
        string $translate,
        string $hash,
        int $eid,
        int $sid,
        string $token
    ) {
        $this->show = $show;
        $this->title = $title;
        $this->season = $season;
        $this->number = $number;
        $this->translate = $translate;
        $this->hash = $hash;
        $this->eid = $eid;
        $this->sid = $sid;
        $this->token = $token;

        $this->setQuality($quality);
    }

    public function getEpisodePath(): string
    {
        // @todo DOWNLOAD_DIR
        return sprintf(
            "%s%s/Season %02d/s%02de%02d %s.mp4",
            $_ENV['DOWNLOAD_DIR'],
            $this->escapePath($this->show),
            $this->season,
            $this->season,
            $this->number,
            $this->escapePath($this->title)
        );
    }

    public function getSeasonPath(): string
    {
        // @todo DOWNLOAD_DIR
        return dirname($this->getEpisodePath());
    }

    /**
     * Set and verify quality of episode
     *
     * @param string $quality
     *
     * @return void
     *
     * @throws QualityException
     */
    public function setQuality(string $quality): void
    {
        if (isset(self::$QUALITY_RANK[$quality])) {
            $this->quality = $quality;
            return;
        }

        throw new QualityException(sprintf("Unknown quality type :: %s", $this->quality));
    }

    /**
     * Return Quality type of episode
     *
     * @return string
     */
    public function getQuality(): string
    {
        return $this->quality;
    }

    /**
     * Compare quality of this episode with another
     *
     * @param string $quality
     *
     * @return bool
     */
    public function isBetterQualityThen(string $quality): bool
    {
        if (self::$QUALITY_RANK[$this->getQuality()] > self::$QUALITY_RANK[$quality]) {
            return true;
        }

        return false;
    }

    /**
     * @return string
     *
     * @throws CurlException
     */
    public function getUrl(): string
    {
        return sprintf(
            'https://%s.soap4.me/%s/%s/%s/',
            $this->getServerId(),
            $this->token,
            $this->eid,
            $this->getHash()
        );
    }

    /**
     * @return string
     */
    public function getShow(): string
    {
        return $this->show;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return int
     */
    public function getSeason(): int
    {
        return $this->season;
    }

    /**
     * @return int
     */
    public function getNumber(): int
    {
        return $this->number;
    }

    /**
     * @return bool
     *
     * @throws CurlException
     */
    public function markAsWatched(): bool
    {
        $payload = [
            'eid' => $this->eid,
            'token' => $this->token,
            'what' => 'mark_watched',
        ];

        $result = json_decode($this->curl('/callback/', $payload), true);

        if (!isset($result['ok'])) {
            throw new CurlException(sprintf(
                "unknown response when mark episode as watched :: %s",
                var_export($result, true)
            ));
        }

        return true;
    }

    /**
     * @return string
     *
     * @throws CurlException
     */
    private function getServerId(): string
    {
        $payload = [
            'do' => 'load',
            'eid' => $this->eid,
            'hash' => $this->getHash(),
            'token' => $this->token,
            'what' => 'player',
        ];

        $res = json_decode($this->curl('/callback/', $payload), true);

        return $res['server'];
    }

    /**
     * @param string $string
     *
     * @return string
     * @todo normalize
     *
     */
    private function escapePath(string $string): string
    {
        $replaced = preg_replace('/[^A-Za-z0-9! _\-]/', ' ', $string);

        $replaced = trim($replaced);

        if (is_null($replaced)) {
            throw new \LogicException(sprintf("Can not escape string :: %s", $string));
        }

        return addslashes($replaced);
    }

    /**
     * Return hash string for episode url
     *
     * @return string
     */
    private function getHash(): string
    {
        return md5(sprintf(
            "%s%s%s%s",
            $this->token,
            $this->eid,
            $this->sid,
            $this->hash
        ));
    }
}