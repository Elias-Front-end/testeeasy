FROM php:8.2-apache

# Habilitar mod_rewrite se necessário
RUN a2enmod rewrite

# Definir diretório de trabalho
WORKDIR /var/www/html

# Definir a raiz do documento para public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public

# Atualizar a configuração do Apache para apontar para o novo DocumentRoot
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Copiar os arquivos da aplicação
COPY . /var/www/html/

# Definir permissões corretas
RUN chown -R www-data:www-data /var/www/html

# Expor a porta 80
EXPOSE 80

# Comando para iniciar o Apache (formato JSON para evitar warnings)
CMD ["apache2-foreground"]
