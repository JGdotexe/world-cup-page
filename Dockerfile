FROM php:8.2-apache

# Habilita extensões do MySQL para o PHP conseguir conversar com o banco
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Habilita o mod_rewrite do Apache (útil para URLs limpas se precisar)
RUN a2enmod rewrite
