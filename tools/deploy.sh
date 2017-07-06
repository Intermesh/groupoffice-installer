#!/bin/bash

#change to script directory
cd "$(dirname "$0")"
cd ..

mkdir -p build
rm -rf build/*

cd build
git clone git@github.com:Intermesh/groupoffice-server.git
cd  groupoffice-server
rm -rf .git*
composer install --no-dev  --optimize-autoloader
cd ..

git clone git@github.com:Intermesh/groupoffice-installer.git
cd  groupoffice-installer
rm -rf .git*
cd ..

git clone git@github.com:Intermesh/groupoffice-webclient.git
cd  groupoffice-webclient
npm install
gulp build

mv build/* ..
cd ..
cp config.js.example config.js
