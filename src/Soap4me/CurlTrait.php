<?php declare(strict_types=1);

namespace Soap4me;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\FileCookieJar;
use GuzzleHttp\Exception\GuzzleException;
use Soap4me\Exception\CurlException;

trait CurlTrait
{
    /** @var string $baseUrl */
    protected $baseUrl = 'https://soap4youand.me';

    /** @var Client */
    protected $client;

    /**
     * @param string $url
     * @param array $data
     *
     * @return string
     *
     * @throws CurlException
     */
    private function curl(string $url, array $data = [])
    {
        $client = $this->getHttpClient();

        $method = "GET";

        $payload = $this->getDefaultOptions();

        if (count($data) > 0) {
            $method = "POST";
            $payload['form_params'] = $data;
        }

        try {
            $r = $client->request($method, $url, $payload);

            if ($r->getStatusCode() !== 200) {
                throw new CurlException(sprintf(
                    'Not 200 responce code | %d | url: %s',
                    $r->getStatusCode(),
                    $url
                ));
            }

            return $r->getBody()->getContents();
        } catch (GuzzleException $e) {
            throw new CurlException(sprintf(
                'Not 200 responce code | %d | url: %s',
                isset($r) ? $r->getStatusCode() : -1,
                $url
            ));
        }
    }

    /**
     * @return Client
     */
    private function getHttpClient(): Client
    {
        if ($this->client === null) {
            $this->client = new Client([
                'base_uri' => $this->baseUrl,
                'timeout' => 5.0,
            ]);
        }

        return $this->client;
    }

    /**
     * Using in tests
     *
     * @param Client $client
     */
    public function setHttpClient(Client $client): void
    {
        $this->client = $client;
    }

    /**
     * Return default options for Guzzle request
     *
     * @return array
     */
    private function getDefaultOptions(): array
    {
        $payload = [
            'cookies' => $this->getCookies(),
            'headers' => [
                'Referer' => 'https://soap4youand.me/',
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:147.0) Gecko/20100101 Firefox/147.0',
            ],
            'allow_redirects' => true,
            'delay' => mt_rand(1, 5) * 1000,
            'version' => 1.0,
            'debug' => true, // @todo to config
        ];
        return $payload;
    }

    /**
     * @return CookieJar
     */
    private function getCookies(): CookieJar
    {
        /** @todo to di */
        return new FileCookieJar($_ENV["COOKIE_FILE"], true);
    }
}
