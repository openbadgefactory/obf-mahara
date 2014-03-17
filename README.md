Instructions for OBF Mahara plugin
==================================

This project uses Composer to manage dependencies. If you don't have Composer installed, run the
following command to install it:

    curl -sS https://getcomposer.org/installer | php

And then install the project dependencies using Composer:

    php composer.phar install

Building
--------

Build task copies the source files to project's build-directory. Building the
plugin is as easy as running the following command in project directory:

    vendor/bin/phing

Testing
-------

TODO: Write sane tests.

How to install
--------------

1. Create a directory to your Mahara-installation's interaction-directory:

        mkdir /[maharadir]/interaction/obf

2. Give web server user write privileges to pki-directory:

        chown www-data:www-data /[maharadir]/interaction/obf/pki

3. Navigate to Administration > Extensions and locate the OBF-plugin below
   "Plugin type: interaction".
4. Click "Install"