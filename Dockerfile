# ── Build stage: install PHP extensions ───────────────────────────────────────
FROM php:8.4-apache AS base

# Install PDO + MySQL driver and clean up apt cache in one layer
RUN docker-php-ext-install pdo pdo_mysql

# Enable Apache mod_rewrite (useful for future routing needs)
RUN a2enmod rewrite

# ── Application ────────────────────────────────────────────────────────────────
WORKDIR /var/www/html

# Copy application source (respects .dockerignore)
COPY . .

# Ensure Apache serves index.html as the default document
RUN echo "DirectoryIndex index.html index.php" \
    > /etc/apache2/conf-enabled/directory-index.conf

EXPOSE 80
