FROM tutum/apache-php

MAINTAINER Stephen Hoogendijk <stephen@tca0.nl>

ENV ALLOW_OVERRIDE=true

RUN DEBIAN_FRONTEND=noninteractive apt-get -qy update

# install additional php extensions
RUN DEBIAN_FRONTEND=noninteractive apt-get -qy install librrd-dev php5-dev
RUN DEBIAN_FRONTEND=noninteractive apt-get -qy install php5-memcached git

# upgrade PHP to 5.6
RUN echo "deb http://archive.ubuntu.com/ubuntu trusty main universe" > /etc/apt/sources.list && \
    DEBIAN_FRONTEND=noninteractive apt-get update && \
    DEBIAN_FRONTEND=noninteractive apt-get -y dist-upgrade && \
    echo "deb http://ppa.launchpad.net/ondrej/php5-5.6/ubuntu trusty main" >> /etc/apt/sources.list && \
    apt-key adv --keyserver keyserver.ubuntu.com --recv-key E5267A6C && \
    DEBIAN_FRONTEND=noninteractive apt-get update

RUN DEBIAN_FRONTEND=noninteractive apt-get -y install libapache2-mod-php5

RUN DEBIAN_FRONTEND=noninteractive apt-get -y install php5 php5-gd php5-ldap \
    php5-sqlite php5-mysql \
    php5-mcrypt php5-xmlrpc php5-memcache php5-intl

RUN DEBIAN_FRONTEND=noninteractive apt-get -qy install php5-dev

# set the apache document root to web/
RUN sed -i "s/DocumentRoot.*/DocumentRoot \/var\/www\/html\/web/g" /etc/apache2/sites-enabled/000-default.conf
RUN rm -rf /app

# Generate the proper locales
RUN locale-gen en_US en_US.utf8 de_DE de_DE.utf8 nl_NL nl_NL.utf8

# Add image configuration and scripts
ADD docker-files/run.sh /run.sh
ADD docker-files/data /
ADD . /app

RUN chmod 755 /*.sh

CMD ["/run.sh"]
