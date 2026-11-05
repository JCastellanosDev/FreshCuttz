# Fresh Cuttz en Vercel (preset Container): FrankenPHP = PHP 8.4 + servidor Caddy.
# Vercel detecta este archivo, construye la imagen y la ejecuta como Vercel Function.
FROM dunglas/frankenphp:1-php8.4-alpine

# pdo_mysql para la base de datos; ca-certificates para la conexión cifrada (DB_SSL=1).
RUN install-php-extensions pdo_mysql \
    && apk add --no-cache ca-certificates \
    && cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY docker/php.ini "$PHP_INI_DIR/conf.d/zz-freshcuttz.ini"
COPY Caddyfile /etc/caddy/Caddyfile

WORKDIR /app
# .dockerignore deja fuera .git, db/ y los archivos de despliegue.
COPY . /app

ENV PORT=80
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
