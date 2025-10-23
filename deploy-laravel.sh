#!/usr/bin/env bash
set -e
BRANCH=${1:-main}
APP_DIR=/home/zender/web/azomacrm.site/public_html
PHP=/usr/bin/php

cd $APP_DIR
git fetch --all
git checkout $BRANCH
git pull origin $BRANCH

# composer
composer install --no-dev --prefer-dist --optimize-autoloader

# migrate and caches
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

# tell horizon to gracefully restart so new code is used
php artisan horizon:terminate

# install node dependencies for react/inertia
npm ci || npm install

# run build
npm run build

# reload services
# systemctl reload php8.3-fpm || true
# systemctl reload nginx || true

echo "Laravel deploy finished."
