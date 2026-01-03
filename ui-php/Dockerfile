FROM registry.access.redhat.com/ubi8/php-82

USER root
RUN dnf install -y php-mysqlnd && dnf clean all

COPY src/ /var/www/html/
CMD ["/usr/libexec/s2i/run"]

