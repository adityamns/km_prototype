# ===================
# Stage 1: Builder
# ===================
FROM php:8.2-fpm-bullseye AS builder

ENV TZ=Asia/Jakarta

# Install PHP extensions and system packages
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    curl \
    gnupg2 \
    libpq-dev \
    libzip-dev \
    zlib1g-dev \
    libicu-dev \
    libonig-dev \
    python3 python3-pip python3-venv \
    build-essential \
    gfortran \
    pkg-config \
    nodejs npm \
    && rm -rf /var/lib/apt/lists/*

# ✅ Install Node.js 20+
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - && \
    apt-get install -y nodejs

# PHP extensions
RUN docker-php-ext-install pdo_pgsql pgsql zip intl

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php && \
    mv composer.phar /usr/local/bin/composer

# Create working directory
WORKDIR /var/www/html

# Copy source code
COPY . .

# Install PHP and Node dependencies
RUN composer install --no-scripts --no-interaction
RUN npm install

# Setup Python environment
RUN cd embedding_service && \
    python3 -m venv myenv && \
    . myenv/bin/activate && \
    pip install --upgrade pip && \
    pip install -r requirements.txt

# ===================
# Stage 2: Runtime
# ===================
FROM php:8.2-fpm-bullseye

ENV TZ=Asia/Jakarta

# Install runtime dependencies
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    libicu-dev \
    zlib1g-dev \
    pkg-config \
    python3 python3-pip \
 && rm -rf /var/lib/apt/lists/*


# Install PHP extensions
RUN docker-php-ext-install pdo_pgsql pgsql zip intl

# Install Composer (again, but lightweight)
RUN curl -sS https://getcomposer.org/installer | php && \
    mv composer.phar /usr/local/bin/composer

WORKDIR /var/www/html

# Copy from build stage
COPY --from=builder /var/www/html /var/www/html

# Activate virtualenv and test
RUN cd embedding_service && \
    . myenv/bin/activate && \
    echo "✅ Python venv ready."

# Salin entrypoint
COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 8000
EXPOSE 8180

CMD ["/usr/local/bin/entrypoint.sh"]

