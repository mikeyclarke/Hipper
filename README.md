# Hipper

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
- migrate – `brew install golang-migrate`
- Ansible – `brew install ansible`

### Configuration

- PHP
    - `/usr/local/etc/php/7.3/php.ini`
        - Set timezone to UTC: add `date.timezone = UTC`
        - Improve PHP security: add `cgi.fix_pathinfo=0`
        - Set a more appropriate session lifetime (4 days): replace `session.gc_maxlifetime = 1440` with `session.gc_maxlifetime = 345600`
- PostgreSQL
    - `/usr/local/var/postgres/postgresql.conf` change `timezone` to `UTC`
    - 'createdb `whoami`'
    - Log into psql and `create database hipper`
    - Run `psql -d hipper -a -f sql/init.sql`
- SSL certs
    - `mkcert -install`
    - `mkcert usehipper.test "*.usehipper.test"`
    - `mkdir -p /usr/local/etc/nginx/ssl`
    - `mv usehipper.test.pem /usr/local/etc/nginx/ssl/hipper.crt`
    - `mv usehipper.test-key.pem /usr/local/etc/nginx/ssl/hipper.key`
- Nginx
    - Run `php bin/console app:generate-vhosts usehipper.test /usr/local/etc/nginx/servers /usr/local/etc/nginx/ssl`

### Set up

- Install composer
    - https://getcomposer.org/download/
    - `mv composer.phar /usr/local/bin/composer`
- Install node
- Run migrations – `make run_migrations`
