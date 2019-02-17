FROM alpine:3.7
MAINTAINER Matt Arnold (typeof.developer@gmail.com)
RUN apk update
RUN apk add nginx
COPY /docker_nginx_conf/hleo-0-assets.conf /etc/nginx/conf.d/hleo-0-assets.conf
COPY /docker_nginx_conf/hleo-1-brand-site.conf /etc/nginx/conf.d/hleo-1-brand-site.conf
COPY /docker_nginx_conf/hleo-2-app.conf /etc/nginx/conf.d/hleo-2-app.conf
COPY wildcard.tryhleo.test.crt /usr/local/etc/nginx/ssl/hleo/wildcard.tryhleo.test.crt
COPY wildcard.tryhleo.test.key /usr/local/etc/nginx/ssl/hleo/wildcard.tryhleo.test.key
COPY naked.tryhleo.test.crt /usr/local/etc/nginx/ssl/hleo/naked.tryhleo.test.crt
COPY naked.tryhleo.test.key /usr/local/etc/nginx/ssl/hleo/naked.tryhleo.test.key
ENTRYPOINT ["/usr/sbin/nginx","-g","pid /tmp/nginx.pid; daemon off;"] 
EXPOSE 80
