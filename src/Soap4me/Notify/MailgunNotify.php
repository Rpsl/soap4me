<?php declare(strict_types=1);

namespace Soap4me\Notify;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;
use Soap4me\Episode;

class MailgunNotify extends AbstractNotify
{
    /** @var string */
    private $from;

    /** @var string */
    private $to;

    /** @var string */
    private $domain;

    /** @var string */
    private $key;

    public function __construct(LoggerInterface $logger, array $config)
    {
        parent::__construct($logger, $config);

        $this->from = $config['from'];
        $this->to = $config['to'];
        $this->domain = $config['domain'];
        $this->key = $config['key'];
    }

    public function notify(Episode $episode)
    {
        $client = new Client([
            'timeout' => 5.0,
        ]);

        $payload = [
            'form_params' => [
                'from' => $this->from,
                'to' => $this->to,
                'subject' => $episode->getShow(),
                'text' => strip_tags($this->getBody($episode)),
                'html' => $this->getBody($episode)
            ],
            'auth' => [
                'api', $this->key
            ],
        ];


        try {
            $r = $client->request("POST", 'https://api.mailgun.net/v3/' . $_ENV['MAILGUN_DOMAIN'] . '/messages', $payload);
        } catch (GuzzleException $e) {
            $this->logger->error($e->getMessage());
        }

        // @todo check errors
        return true;
    }

    private function getBody(Episode $episode)
    {
        return sprintf(
            "<html><p>Серия %s закачана.</p><p>%s</p></html>",
            $episode->getShow(),
            $episode->getEpisodePath()
        );
    }
}
