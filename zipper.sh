#!/usr/bin/env bash
wget https://github.com/ggoop/gmf-laravel/archive/master.zip
unzip master.zip -d working
cd working/gmf-laravel-master
composer install
zip -ry ../../gmf-laravel-craft.zip .
cd ../..
mv gmf-laravel-craft.zip public/gmf-laravel-craft.zip
rm -rf working
rm master.zip
