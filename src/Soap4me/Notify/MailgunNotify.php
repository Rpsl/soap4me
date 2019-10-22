<?php declare(strict_types=1);

namespace Soap4me\Notify;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;
use Soap4me\Episode;

class MailgunNotify extends AbstractNotify
{
    public function notify(Episode $episode): bool
    {
        $client = new Client([
            'timeout' => 5.0,
        ]);

        $payload = [
            'form_params' => [
                'from' => $this->config['from'],
                'to' => $this->config['to'],
                'subject' => $episode->getShow(),
                'text' => strip_tags($this->getBody($episode)),
                'html' => $this->getBody($episode),
            ],
            'auth' => [
                'api',
                $this->config['key'],
            ],
        ];

        $url = sprintf("https://api.mailgun.net/v3/%s/messages", $this->config['domain']);

        try {
            $r = $client->request("POST", $url, $payload);
        } catch (GuzzleException $e) {
            $this->logger->error($e->getMessage());
        }

        // @todo check errors
        return true;
    }

    /**
     * Return html body for email notifycation
     *
     * @param Episode $episode
     *
     * @return string
     */
    private function getBody(Episode $episode): string
    {
        return sprintf(
            "<html><p>Серия %s закачана.</p><p>%s</p></html>",
            $episode->getShow(),
            $episode->getEpisodePath()
        );
    }
}
