<?php declare(strict_types=1);

namespace Soap4me;

use GuzzleHttp\Cookie\FileCookieJar;
use Soap4me\Exception\CurlException;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\GuzzleException;

trait CurlTrait
{
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
        $client = new Client([
            'base_uri' => 'https://soap4.me',
            'timeout' => 5.0,
        ]);

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
     * Return default options for Guzzle request
     *
     * @return array
     */
    private function getDefaultOptions(): array
    {
        $payload = [
            'cookies' => $this->getCookies(),
            'headers' => [
                'Referer' => 'https://soap4.me/',
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.169 Safari/537.36',
            ],
            'allow_redirects' => true,
            'delay' => mt_rand(1, 5) * 1000,
            'version' => 1.0,
            'debug' => false, // @todo to config
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