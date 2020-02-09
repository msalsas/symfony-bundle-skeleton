Symfony Bundle Skeleton
========================

The "Symfony Bundle Skeleton" is an application for creating reusable Symfony bundles.
Forked from [symfony/demo][1]

Requirements
------------

  * PHP 7.2.9 or higher;
  * PDO-SQLite PHP extension enabled;
  * and the [usual Symfony application requirements][2].

Installation
------------

```bash
$ git clone https://github.com/msalsas/symfony-bundle-skeleton.git
```

Install Composer dependencies:

```bash
$ composer install
```

Usage
-----

Run this command to create the new bundle in `/lib`:

```bash
$ php bin/console skeleton-bundle:create
```

You will be asked for some needed arguments for the bundle structure and files.

Then, just cd to the new created bundle:

```bash
$ cd lib/your-namespace # E.g. cd lib/acme/foo-bundle
```
And check your already created git repository:

```bash
$ git status
```
Now you can begin to develop over this path (your bundle path).
Check for the TODO's comments in all files and make the needed changes for your bundle.

There is just one file you have to edit outside your bundle path: 

`config/packages/{your-bundle}.yaml`

This file includes your bundle configuration.

Once your ready, create a new repository (Github or whatever) and add the remote url to your git config:

```bash
$ git add .
$ git commit -m "First commit"
$ git remote add origin your-repository-url.git
$ git push -u origin master
```

There's no need to configure anything to run the application. If you have
[installed Symfony][4], run this command and access the application in your
browser at the given URL (<https://localhost:8000> by default):

```bash
$ cd symfony-bundle-skeleton/
$ symfony serve
```

If you don't have the Symfony binary installed, run `php -S localhost:8000 -t public/`
to use the built-in PHP web server or [configure a web server][3] like Nginx or
Apache to run the application.

Tests
-----

Execute this command to run tests:

```bash
$ cd symfony-bundle-skeleton/
$ ./bin/phpunit
```

[1]: https://github.com/symfony/demo
[2]: https://symfony.com/doc/current/reference/requirements.html
[3]: https://symfony.com/doc/current/cookbook/configuration/web_server_configuration.html
[4]: https://symfony.com/download
[5]: https://github.com/symfony/webpack-encore
