# Soap4.me downloader

**Для работы необходим премиум аккаунт**

## Зачем?

Мне удобно когда все серии скачиваются на сетевой хранилище 

## Что умеет?

- Автоматически скачивать серии в максимальном качестве 
- Уведомлять об успешной закачке на почту
- Раскладывать сериалы по папкам: `/Soap4.me/American Dad/Season1/s01e02 Episode Name.mp4`

# Настройка

- Скопируйте и отредактируйте файл `.env`
- Добавьте в cron запуск файла soap4me.php
- Зарегистрируйтесь на [mailgun.org](https://mailgun.org), если хотите отправлять уведомления на почту.


```dotenv
DOWNLOAD_DIR="downloads/"
COOKIE_FILE="cookie.json"
LOG_FILE="downloads/downloads.log"

SOAP_LOGIN=""
SOAP_PASSWORD=""

# SD, HD, fullHD, 4k UHD
MAX_QUALITY="fullHD"

NOTIFY_EMAIL="mail@me.com"

MAILGUN_DOMAIN="domain.mailgun.org"
MAILGUN_FROM="Soap4me downloader <soap4me@domain.mailgun.org>"
MAILGUN_KEY=""
```

# Docker

Build image from sources
```bash
$ docker build -t soap4me:latest .
```

Pull image from [hub.docker.com](https://hub.docker.com/repository/docker/rpsl/soap4me)
```bash
$ docker pull rpsl/soap4me:latest
```

Run tests
```bash
$ docker run --rm -it -v $(pwd):/app/ --name soap4me --entrypoint 'bash' soap4me:latest -c 'composer update && composer install --prefer-dist --no-progress && ./vendor/bin/phpunit -c .'
```
Run phpstan
```bash
$ docker run --rm -it -v $(pwd):/app/ --name soap4me --entrypoint 'bash' soap4me:latest -c 'composer update && composer install --prefer-dist --no-progress && ./vendor/bin/phpstan analyze --level max ./src ./tests'
```

Docker env
```bash
docker run --rm -it -v $(pwd):/app/ --name soap4me --entrypoint 'bash' soap4me:latest
```

Run downloader (not daemon mode)
```bash
$ docker run --rm -it --name soap4me -v $(pwd)/downloads:/app/downloads/ -v $(pwd)/.env:/app/.env -v $(pwd)/cookie.json:/app/cookie.json soap4me:latest
```
