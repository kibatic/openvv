# Image de base Kibatic basée sur l'image PHP officielle (php:8.5-fpm-trixie).
# Elle fournit déjà : nginx, supervisor, composer, l'entrypoint, et les
# extensions intl / bcmath / zip. Les extensions curl / ctype / iconv sont
# natives à l'image PHP officielle.
FROM kibatic/symfony:8.5-fpm-debian AS base

# Extensions et outils système supplémentaires propres au projet :
# - pdo_pgsql / pgsql : connexion PostgreSQL (Doctrine)
# - imagemagick : binaire `convert` appelé par MediaManager (filtre luminosité + vignettes)
# - libpq-dev : en-têtes nécessaires à la compilation de pdo_pgsql / pgsql
# - acl / gnupg2 : utilitaires (gestion des droits, dépôts apt tiers)
RUN apt-get -qqq update && DEBIAN_FRONTEND=noninteractive apt-get install -qqq -y --no-install-recommends \
        acl \
        gnupg2 \
        libpq-dev \
        imagemagick && \
    docker-php-ext-install pdo_pgsql pgsql && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Symfony CLI
RUN curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.deb.sh' | bash && \
    apt-get install -y symfony-cli && \
    apt-get clean && rm -rf /var/lib/apt/lists/*

# NodeJS 22 LTS + Yarn (build Webpack Encore)
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - && \
    apt-get install -y nodejs && \
    corepack enable && \
    apt-get clean && rm -rf /var/lib/apt/lists/*

RUN git config --global user.email "dev@localhost" && \
    git config --global user.name "dev"

# nginx : remplace la conf globale (taille max d'upload portée à 100M pour les panoramas)
ADD docker/nginx.conf /etc/nginx/nginx.conf
# Réglages PHP : sur l'image officielle, le conf.d partagé CLI + FPM est dans $PHP_INI_DIR
ADD docker/20-tuning.ini /usr/local/etc/php/conf.d/20-tuning.ini

# hack : si le CMD est pris dans le cache, il ajoute un bash -c #(nop) devant la commande
# => on remet la commande ici pour qu'elle soit rebuildée à chaque fois
# cf commentaires de https://stackoverflow.com/questions/66638179/docker-image-command-starts-with-bin-sh-c-nop-cmd
CMD ["/entrypoint.sh"]

FROM base AS dev

ENV PERFORMANCE_OPTIM=false

# Xdebug : compilé via PECL ($PHPIZE_DEPS fournit les outils de build de l'image officielle).
# Le mode `trigger` n'active le débogueur que sur demande : aucun surcoût en temps normal.
RUN apt-get -qqq update && DEBIAN_FRONTEND=noninteractive apt-get install -qqq -y --no-install-recommends \
        $PHPIZE_DEPS && \
    pecl install xdebug && \
    docker-php-ext-enable xdebug && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*
ADD docker/xdebug.ini /usr/local/etc/php/conf.d/zz-xdebug.ini

FROM base AS prod

ENV PERFORMANCE_OPTIM=true

ADD . /var/www
