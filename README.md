File renamer
============

A quick and dirty command to batch rename files (mainly images) based on either their
exif creation date or creation date.

It sorts them into folders based on year and month, then the images are saved sequentially
in the folder.

If it runs into any errors, it'll skip the file.


Dependencies
------------

It's built in PHP and uses [composer](http://getcomposer.org) to manage dependencies.


Setup
-----

Install composer, clone the repo and run `php composer.phar install` to install its dependencies.


Usage
-----

Run `bin/rename --src=<source_dir> --dest=<destination_dir>`


Options
-------

- `--src` is the full directory path to where the files are located
- `--dest` is the full directory path to where the files will be moved to after renaming
