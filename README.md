# soap4.me downloader

Это форкнутый и переписанный, на скорую руку, скрипт [TurboLoader](https://github.com/Rpsl/turboload), который, как можно догадаться из названия, автоматически скачивает сериалы с сервиса [soap4.me](http://soap4.me)

**Для работы необходим премиум аккаунт.**

# Зачем?

Мне удобно когда серия скачивается автоматически на мой nas

# Что умеет?

 - Автоматически скачивать серии в максимальном качестве 
 - Уведомлять об успешной закачке на почту
 - Раскладывать сериалы по папкам: /Soap4.me/American Dad/Season1/s01e02 Episod Name.mp4

# Настройка

 - Скопируйте и отредактируйте файл [config.example.php](https://github.com/Rpsl/soap4me/blob/master/config.example.php) в config.php
 - Добавьте в cron запуск файла soap4me.php
 - Зарегистрируйтесь на [mailgun.org](https://mailgun.org), если хотите отправлять уведомления на почту.

# config.php

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

 
# ToDo
Выбор озвучки для разных сериалов. Какие качать с переводом, а какие в оригинале.
Убрать все упоминания о Turboload.
... ?
