<?php

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Symfony\Component\Dotenv\Dotenv;

use Soap4me\Parser;
use Soap4me\Downloader;
use Soap4me\DownloaderTransport\Aria2;
use Soap4me\Notify\MailgunNotify;

setlocale(LC_CTYPE, "en_US.UTF-8");
date_default_timezone_set('Europe/Moscow');

include_once 'vendor/autoload.php';

// @todo pid ?

$dotenv = new Dotenv();
$dotenv->load(__DIR__ . '/.env');

try {
    $log = new Logger('logger');
    // @todo log level to params
    $log->pushHandler(new StreamHandler($_ENV["LOG_FILE"], Logger::DEBUG));
} catch (Exception $e) {
}

try {
    $log->info('Start processing');

    // @todo need DI
    $parser = new Parser(
        $log,
        $_ENV['SOAP_LOGIN'],
        $_ENV['SOAP_PASSWORD']
    );

    $transport = new Aria2(
        $log,
        new Filesystem(new Local(realpath(dirname($_ENV["DOWNLOAD_DIR"]))))
    );

    $notify = new MailgunNotify($log, [
        'from' => $_ENV["MAILGUN_FROM"],
        'to' => $_ENV["NOTIFY_EMAIL"],
        'domain' => $_ENV["MAILGUN_DOMAIN"],
        'key' => $_ENV["MAILGUN_KEY"],
    ]);

    (new Downloader($log, $transport))
        ->addBatch($parser->findUnwatched())
        ->setNotify($notify)
        ->download();

    $log->info("Finish");
} catch (Exception $e) {
    $log->error($e->getMessage());
    throw new $e;
}