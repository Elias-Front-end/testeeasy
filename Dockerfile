FROM php:8.2-apache

# Habilitar mod_rewrite se necessário
RUN a2enmod rewrite

# Copiar os arquivos da aplicação para o diretório web
COPY . /var/www/html/

# Definir permissões corretas
RUN chown -R www-data:www-data /var/www/html

# Expor a porta 80
EXPOSE 80
