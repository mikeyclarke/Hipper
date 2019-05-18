# Lithos

## Homebrew installation

### Installation

- PHP 7.3 – `brew install php`
- nginx with more headers and http2 modules – `brew install --with-headers-more-module --with-http2 nginx-full`
- PostgreSQL – `brew install postgresql`
- Redis – `brew install redis`
- Redis PHP extension – `pecl install redis`
- mkcert – `brew install mkcert`
- nss – `brew install nss`

### Configuration

- PostgreSQL – `/usr/local/var/postgres/postgresql.conf` change `timezone` to `UTC`

### Set up

- Install composer
    - https://getcomposer.org/download/
    - `mv composer.phar /usr/local/bin/composer`
- Install node

## Make targets

| make target               | description                                 |
|---------------------------|---------------------------------------------|
| `make install_dependencies` | Installs packages from composer and yarn    |
| `make server`               | Runs the dev server                         |
| `make run`                  | Runs webpack once and starts the dev server |
| `make test_js`              | Runs the javascript unit tests and watches  |
| `make test_js_single_run`   | Single run for javascript unit tests        |
| `make webpack`              | Does a one off webpack build                |
| `make phpcs`                | Run php code sniffer                        |
