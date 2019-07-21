# Lithos

## Homebrew installation

### Installation

- PHP 7.3 – `brew install php`
- nginx with more headers and http2 modules
    - `brew tap denji/nginx`
    - `brew install --with-headers-more-module --with-http2 nginx-full`
- PostgreSQL – `brew install postgresql`
- Redis – `brew install redis`
- Redis PHP extension – `pecl install redis`
- mkcert – `brew install mkcert`
- nss – `brew install nss`
- dnsmasq
    - `brew install dnsmasq`
    - Add `address=/.test/127.0.0.1` to `/usr/local/etc/dnsmasq.conf`
    - Create `mkdir -p /etc/resolver`
    - Create `/etc/resolver/test`
    - Add `nameserver 127.0.0.1` to `/etc/resolver/test`
    - Restart macOS

### Configuration

- PHP
    - `/usr/local/etc/php/7.3/php.ini`
        - Set timezone to UTC: add `date.timezone = UTC`
        - Improve PHP security: add `cgi.fix_pathinfo=0`
        - Set a more appropriate session lifetime (4 days): replace `session.gc_maxlifetime = 1440` with `session.gc_maxlifetime = 345600`
- PostgreSQL
    - `/usr/local/var/postgres/postgresql.conf` change `timezone` to `UTC`
    - 'createdb `whoami`'
    - Log into psql and `create database hleo`
    - Run `psql -d hleo -a -f sql/init.sql`
- SSL certs
    - `mkcert -install`
    - `mkcert tryhleo.test "*.tryhleo.test"`
    - `mkdir -p /usr/local/etc/nginx/ssl`
    - `mv tryhleo.test.pem /usr/local/etc/nginx/ssl/hleo.crt`
    - `mv tryhleo.test-key.pem /usr/local/etc/nginx/ssl/hleo.key`
- Nginx
    - Run `php bin/console app:generate-vhosts tryhleo.test /usr/local/etc/nginx/servers /usr/local/etc/nginx/ssl`

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
