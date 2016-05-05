<?php

SomeShit::$config = [
	'download_dir' => realpath(__DIR__) . '/downloads',
	'cookie_file' => realpath(__DIR__) . '/cookie.txt',
	'log_file' => realpath(__DIR__) . '/downloads.txt',

	'login' => '',
	'password' => '',

	'email' => ['mail@me.com'],
	'mailgun' => [
		'domain' => 'my.mailgun.org',
		'api-key' => 'api:key-123123123',
	],
];
