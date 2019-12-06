# soap4.me downloader

**Для работы необходим премиум аккаунт**

Это форкнутый и переписанный, на скорую руку, скрипт [TurboLoader](https://github.com/Rpsl/turboload), который, как можно догадаться из названия, автоматически скачивает сериалы с сервиса [soap4.me](http://soap4.me)

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

NOTIFY_EMAIL="mail@me.com"

MAILGUN_DOMAIN="domain.mailgun.org"
MAILGUN_FROM="Turboloader <turboload@domain.mailgun.org>"
MAILGUN_KEY=""
```


### Docker

```bash
docker build -t soap4me:latest .
docker run --rm -it --name soap4me -v $(pwd)/downloads:/app/downloads/ -v $(pwd)/.env:/app/.env -v $(pwd)/cookie.json:/app/cookie.json soap4me:latest
```
