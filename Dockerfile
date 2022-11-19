FROM kibatic/symfony:8.1 AS base

RUN apt-get -qqq update && DEBIAN_FRONTEND=noninteractive apt-get install -qqq -y \
        acl \
        php8.1-pgsql \
        php8.1-curl \
        php8.1-zip \
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

# hack : si le CMD est pris dans le cache, il ajoute un bach -c #(nop) devant la commande
# => on remet la commande ici pour qu'elle soit rebuildée à chaque fois
# cf commentaires de https://stackoverflow.com/questions/66638179/docker-image-command-starts-with-bin-sh-c-nop-cmd
CMD ["/entrypoint.sh"]

FROM base AS dev

ENV PERFORMANCE_OPTIM false

# XDebug
RUN apt-get -qqq update && DEBIAN_FRONTEND=noninteractive apt-get install -qqq -y \
        php8.1-xdebug && \
        apt-get clean && \
        rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* && \
        rm /etc/php/8.1/fpm/conf.d/20-xdebug.ini && \
        rm /etc/php/8.1/cli/conf.d/20-xdebug.ini

FROM base AS prod

ENV PERFORMANCE_OPTIM true

ADD . /var/www
