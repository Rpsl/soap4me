<?php declare(strict_types=1);

namespace Soap4me;

use phpQuery;
use Psr\Log\LoggerInterface;
use Soap4me\Exception\CurlException;
use Soap4me\Exception\ParseException;
use Soap4me\Exception\QualityException;

/**
 * @property-read string $baseUrl
 */
class Parser
{
    use CurlTrait;

    /** @var LoggerInterface */
    private $logger;

    /** @var string */
    private $login;

    /** @var string */
    private $password;

    public function __construct(LoggerInterface $logger, string $login, string $password)
    {
        $this->logger = $logger;
        $this->login = $login;
        $this->password = $password;

        if ($this->isNeedLogin()) {
            $this->login();
        }
    }

    /**
     * Searches for unwatched episodes
     *
     * @return Episode[]
     */
    public function findUnwatched()
    {
        try {
            $html = $this->curl('/new/my/unwatched/');
        } catch (CurlException $e) {
            $this->logger->error($e->getMessage());
            return [];
        }

        $res = phpQuery::newDocumentHTML($html, $charset = 'utf-8');

        $token = trim($res->find('#token')->attr('data:token'));

        $episodes = $res->find('li.ep');

        $unwatched = [];

        foreach ($episodes as $ep) {
            try {
                $unwatched[] = new Episode(
                    trim(pq($ep)->find('.soap')->text()),
                    trim(pq($ep)->find('.en')->text()),
                    (int)trim(pq($ep)->find('.play')->attr('data:season')),
                    $this->parseEpisodeNumber(trim(pq($ep)->find('.nums')->text())),
                    trim(pq($ep)->find('.quality')->text()),
                    trim(pq($ep)->find('.translate')->text()),
                    trim(pq($ep)->find('.play')->attr('data:hash')),
                    (int)trim(pq($ep)->find('.play')->attr('data:eid')),
                    (int)trim(pq($ep)->find('.play')->attr('data:sid')),
                    $token
                );
            } catch (QualityException|ParseException $e) {
                $this->logger->error($e->getMessage());
            }
        }

        return $unwatched;
    }

    /**
     * @return bool
     */
    private function isNeedLogin(): bool
    {
        $result = null;

        try {
            $result = $this->curl('/');

            if ((bool)preg_match('~(вход на сайт)~usi', $result)) {
                return true;
            }

            return false;
        } catch (CurlException $e) {
            $this->logger->error(sprintf('Check login is error :: %s :: %s', $e->getMessage(), $result));
        }
    }

    /**
     * @return bool
     */
    private function login(): bool
    {
        try {
            $res = $this->curl('/login/', [
                'login' => $this->login,
                'password' => $this->password,
            ]);
        } catch (CurlException $e) {
            $this->logger->error($e->getMessage());
            return false;
        }

        // @todo check that login is successful
        return true;
    }

    /**
     * @param string $string
     *
     * @return int
     *
     * @throws ParseException
     */
    private function parseEpisodeNumber(string $string): int
    {
        if (!(bool)preg_match('/s([0-9]+)e([0-9]+)/', $string, $matches)) {
            throw new ParseException(sprintf("Can't parse episode number :: %s", $string));
        }

        return (int)$matches[2];
    }
}