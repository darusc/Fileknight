FROM caddy:2-builder-alpine AS builder
RUN xcaddy build

FROM caddy:2-alpine
RUN set -eux; apk add --no-cache nss-tools
WORKDIR /srv/app
COPY --from=builder /usr/bin/caddy /usr/bin/caddy
COPY docker/caddy/Caddyfile.dev /etc/caddy/Caddyfile
COPY docker/caddy/Caddyfile.prod /etc/caddy/Caddyfile