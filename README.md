# Hipper

## Homebrew installation

### Installation

- PHP 7.4 – `brew install php@7.4`
- nginx with more headers and http2 modules
    - `brew tap denji/nginx`
    - `brew install --with-headers-more-module --with-http2 nginx-full`
- PostgreSQL – `brew install postgresql@12`
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
    - `/usr/local/etc/php/7.4/php.ini`
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
    - `mkdir -p /usr/local/etc/nginx/ssl/hipper`
    - `mv usehipper.test.pem /usr/local/etc/nginx/ssl/hipper/fullchain.pem`
    - `mv usehipper.test-key.pem /usr/local/etc/nginx/ssl/hipper/privkey.pem`
- Nginx
    - Run `php bin/console app:generate-vhosts usehipper.test /usr/local/etc/nginx/servers /usr/local/etc/nginx/ssl/hipper`
- Redis
    - `/usr/local/etc/redis.conf` add `requirepass '<YOUR_PASSWORD>'` and restart Redis; you’ll need to add this password to your `.env.local`

### Set up

- Install composer
    - https://getcomposer.org/download/
    - `mv composer.phar /usr/local/bin/composer`
- Install node
- Run migrations – `make run_migrations`
