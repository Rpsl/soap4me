<?php

	SomeShit::$config = array(
		'download_dir' => realpath( __DIR__ ) . '/downloads',
		'cookie_file'  => realpath( __DIR__ ) . '/cookie.txt',
		'log_file'     => realpath( __DIR__ ) . '/downloads.txt',

		'login'     => '',
		'password'  => '',

		'email'         => array('mail@me.com'),
		'mailgun'       => array(
			'domain'    => 'my.mailgun.org',
			'api-key'   => 'api:key-123123123'
		),
	);
