FROM kibatic/symfony:8.2 AS base

RUN apt-get -qqq update && DEBIAN_FRONTEND=noninteractive apt-get install -qqq -y \
        acl \
        php8.2-pgsql \
        php8.2-curl \
        php8.2-zip \
    	php8.2-imagick \
        imagemagick \
        gnupg2 && \
        apt-get clean && \
        rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Symfony CLI
RUN curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.deb.sh' | bash
RUN apt install symfony-cli

# NodeJS
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash -
RUN apt-get install -y nodejs

# Yarn
RUN corepack enable

RUN git config --global user.email "dev@localhost"
RUN git config --global user.name "dev"

ADD docker/nginx.conf /etc/nginx/nginx.conf
ADD docker/20-tuning.ini /etc/php/8.2/fpm/conf.d/20-tuning.ini

# hack : si le CMD est pris dans le cache, il ajoute un bach -c #(nop) devant la commande
# => on remet la commande ici pour qu'elle soit rebuildée à chaque fois
# cf commentaires de https://stackoverflow.com/questions/66638179/docker-image-command-starts-with-bin-sh-c-nop-cmd
CMD ["/entrypoint.sh"]

FROM base AS dev

ENV PERFORMANCE_OPTIM false

# XDebug
RUN apt-get -qqq update && DEBIAN_FRONTEND=noninteractive apt-get install -qqq -y \
        php8.2-xdebug && \
        apt-get clean && \
        rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* && \
        rm /etc/php/8.2/fpm/conf.d/20-xdebug.ini && \
        rm /etc/php/8.2/cli/conf.d/20-xdebug.ini

FROM base AS prod

ENV PERFORMANCE_OPTIM true

ADD . /var/www
