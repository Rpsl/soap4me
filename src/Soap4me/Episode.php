<?php declare(strict_types=1);

namespace Soap4me;

use LogicException;
use Soap4me\Exception\CurlException;

/**
 * @property-read string $baseUrl
 */
class Episode
{
    use CurlTrait;

    /** @var string $show TV Show name */
    private string $show;

    /** @var string $title TV Show episode title */
    private string $title;

    /** @var int $season TV Show season number */
    private int $season;

    /** @var int $number TV Show episode number */
    private int $number;

    /** @var Quality $quality TV Show episode quality */
    private Quality $quality;

    /** @var string $translate TV Show sound translate */
    private string $translate;

    /** @var string $hash of downloaded file */
    private string $hash;

    /** @var int $eid of downloaded file */
    private int $eid;

    /** @var int $sid of downloaded file */
    private int $sid;

    /** @var string $token for video player */
    private string $token;

    /**
     * Episode constructor.
     *
     * @param string $show
     * @param string $title
     * @param int $season
     * @param int $number
     * @param Quality $quality
     * @param string $translate
     * @param string $hash
     * @param int $eid
     * @param int $sid
     * @param string $token
     */
    public function __construct(
        string $show,
        string $title,
        int $season,
        int $number,
        Quality $quality,
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
        $this->quality = $quality;
        $this->translate = $translate;
        $this->hash = $hash;
        $this->eid = $eid;
        $this->sid = $sid;
        $this->token = $token;

    }

    public function getEpisodePath(): string
    {
        // @todo DOWNLOAD_DIR
        return sprintf(
            '%s%s/Season %02d/s%02de%02d %s.mp4',
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
     * Return Quality type of episode
     *
     * @return Quality
     */
    public function getQuality(): Quality
    {
        return $this->quality;
    }

    

    /**
     * @return string
     *
     * @throws CurlException
     */
    public function getUrl(): string
    {
        return sprintf(
            'https://%s.soap4youand.me/%s/%s/%s/',
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
     * @return string
     */
    public function getTranslate():string
    {
        return $this->translate;
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

        if (!is_array($result) && !isset($result['ok'])) {
            throw new CurlException(sprintf(
                'unknown response when mark episode as watched :: %s',
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

        if (is_null($replaced)) {
            throw new LogicException(sprintf('Can not escape string :: %s', $string));
        }

        return addslashes(trim($replaced));
    }

    /**
     * Return hash string for episode url
     *
     * @return string
     */
    private function getHash(): string
    {
        return md5(sprintf(
            '%s%s%s%s',
            $this->token,
            $this->eid,
            $this->sid,
            $this->hash
        ));
    }
}