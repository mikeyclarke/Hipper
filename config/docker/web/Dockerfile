FROM alpine:3.7
MAINTAINER Matt Arnold (typeof.developer@gmail.com)
RUN apk update
RUN apk add nginx
COPY /docker_nginx_conf/hipper-0-assets.conf /etc/nginx/conf.d/hipper-0-assets.conf
COPY /docker_nginx_conf/hipper-1-brand-site.conf /etc/nginx/conf.d/hipper-1-brand-site.conf
COPY /docker_nginx_conf/hipper-2-app.conf /etc/nginx/conf.d/hipper-2-app.conf
COPY wildcard.usehipper.test.crt /usr/local/etc/nginx/ssl/hipper/wildcard.usehipper.test.crt
COPY wildcard.usehipper.test.key /usr/local/etc/nginx/ssl/hipper/wildcard.usehipper.test.key
COPY naked.usehipper.test.crt /usr/local/etc/nginx/ssl/hipper/naked.usehipper.test.crt
COPY naked.usehipper.test.key /usr/local/etc/nginx/ssl/hipper/naked.usehipper.test.key
ENTRYPOINT ["/usr/sbin/nginx","-g","pid /tmp/nginx.pid; daemon off;"] 
EXPOSE 80
