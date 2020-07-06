# Winbooks On Web PHP Client

## Installation

```
composer require whitecube/winbooks-on-web-php-client
```

## Usage

```php
use Winbooks\Winbooks;

$winbooks = new Winbooks('oauth-token');
```

You can set the folder once and it will be used for all subsequent requests

```php
$winbooks->folder('TEST_FOLDER');

$winbooks->get(/*...*/)
```

To get all results from an object model, use the `all($object_model)` method.

```php
$winbooks->all('Customers');
```

To get a single result from an object model, use the `get($object_model, $code)` method.

```php
$winbooks->get('Customer', 'VLADIMIR');
```


## Tests

This project uses [PEST](https://pestphp.com/) for tests.

To run the tests:
```
./vendor/bin/test
```

with code coverage (needs xdebug)
```
./vendor.bin/test --coverage
```


## 💖 Sponsorships

If you are reliant on this package in your production applications, consider [sponsoring us](https://github.com/sponsors/whitecube)! It is the best way to help us keep doing what we love to do: making great open source software.

## Contributing

Feel free to suggest changes, ask for new features or fix bugs yourself. We're sure there are still a lot of improvements that could be made, and we would be very happy to merge useful pull requests.

Thanks!

## Made with ❤️ for open source

At [Whitecube](https://www.whitecube.be) we use a lot of open source software as part of our daily work.
So when we have an opportunity to give something back, we're super excited!

We hope you will enjoy this small contribution from us and would love to [hear from you](mailto:hello@whitecube.be) if you find it useful in your projects. Follow us on [Twitter](https://twitter.com/whitecube_be) for more updates!
