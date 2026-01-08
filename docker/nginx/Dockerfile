FROM nginx:1.27-alpine

WORKDIR /var/www/html

# Copia apenas a pasta public para o container do Nginx
COPY app/public /var/www/html/public

# Configuração do Nginx para ECS (fastcgi para php-fpm no mesmo task)
COPY docker/nginx/default.ecs.conf /etc/nginx/conf.d/default.conf

EXPOSE 80
