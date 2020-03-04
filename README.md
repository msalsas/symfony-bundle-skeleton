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

Examples
--------

**Macro example:**

Add this snippet into `templates/blog/index.html.twig` before `<article class="post">`:

```twig
{% import "@{your_namespace}/{your_namespace}_widget.html.twig" as {your_namespace}_widget %}
{{ {your_namespace}_widget.bar('https://github.com/msalsas/symfony-bundle-skeleton', 34) }}
```

Go to <http://127.0.0.1:8000/en/blog/>

You will see the logic of the `bar` macro applied to each blog post

**Persisting entities example:**

There is an already created example with a Car entity. If you run:

```bash
$ php bin/console doctrine:schema:update --force
```

a new table `{you-domain}_car` will be created

To create new entities you can use the created service. E.g. create a route in

`src/Controller/BlogController.php`

with the next action;

```php
/**
 * @Route("/car/random", methods="GET", name="create_random_car")
 **/
public function postRandomCar(Service $service): Response
{
    $cars = ['BMW' => ['Serie 3', 'X5'], 'Mercedes' => ['Benz CLA', 'Benz GLS'], 'Seat' => ['Leon', 'Ibiza'], 'Toyota' => ['Corolla', 'Yaris']];
    $brand_index = rand(0, 3);
    $model_index = rand(0,1);
    $brand = array_keys($cars)[$brand_index];
    $model = $cars[$brand][$model_index];
    $service->createCar($brand, $model);
    $cars = $service->getCars();

    return $this->render('cars/index.html.twig', ['cars' => $cars]);
}
``` 
*Use the service from your namespace*

And now create a new twig template:

`templates/cars/index.html.twig`

and add the next content:

```twig
{% extends 'base.html.twig' %}

{% block body_id 'blog_index' %}

{% block main %}
    <h2>Create random car:</h2>
    <h3><a href="{{ path('create_random_car') }}">Create</a></h3>
    <br/>
    <h2>Created cars:</h2>
    {% for car in cars %}
        <h3>Car from {{ car.user and car.user.username ? car.user.username : 'anon.' }}</h3>
        <p>Model: {{ car.model }}</p>
        <p>Brand: {{ car.brand }}</p>
    {% endfor %}
{% endblock %}
```

If you go to <http://127.0.0.1:8000/en/blog/car/random> you will see an already created car and a link to create random cars.
As you click on the link, new random cars are created.
If you go to the backend and log in and then go back and create new cars, you will see that the related user is the logged in user.

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
