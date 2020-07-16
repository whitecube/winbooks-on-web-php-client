# Winbooks On Web PHP Client

![Tests](https://github.com/whitecube/winbooks-on-web-php-client/workflows/Tests/badge.svg)

## Installation

```
composer require whitecube/winbooks-on-web-php-client
```

## Usage

### Authentication
Before you can do anything, you have to authenticate with the API. This is done with OAuth 2.0, so you will need the e-mail and the Exchange Token provided by Winbooks on Web. You can get those by following [these steps](https://help.winbooks.be/display/DEV/Grant+an+access+to+your+license).

When you have those ready to go, you can use them to ask the API to grant you an Access Token and a Refresh Token. The Access Token is necessary to authorise every request, and the Refresh Token is used to get a new Access Token if it has expired.

When you create an instance of the Winbooks client, you can give it the Access Token and the Refresh Token right away if you have them. If you don't, you can simply call `authenticate($email, $exchange_token)` afterwards, which will grant you those tokens which you should then save and reuse the next time you make an instance of the client.

```php
// $access_token and $refresh_token can be null if you do not have them yet
$winbooks = new Winbooks($access_token, $refresh_token);

if(!$winbooks->authenticated()) {
    $tokens = $winbooks->authenticate($email, $exchange_token);
    // Store the tokens somewhere safe
}

// Now you can start using the API
```

### Specifying the folder
You can set the folder once and it will be used for all subsequent requests.

```php
$winbooks->folder('TEST_FOLDER');

$winbooks->get(/*...*/)
```

### Getting data
All getter methods will return the JSON data directly from WoW, already decoded.

To get all results from an object model, use the `all($object_model)` method.

```php
$customers = $winbooks->all('Customers');
```

To get a single result from an object model, use the `get($object_model, $code)` method.
> Note: you can substitute $code for the ID if you have it.

```php
$vlad = $winbooks->get('Customer', 'VLADIMIR');
// With ID
$vlad = $winbooks->get('Customer', '4713a22f-ebc0-ea11-80c7-0050s68cc4a2');
```

### Inserting data
A generic way to insert data is by using the `add($object_model, $code, $data)` method:

```php
$winbooks->add('Customer', 'VLADIMIR', [
    'Memo' => 'A Memo for Vladimir',
    // ...
]);

// You can also add multiple objects at once:

$winbooks->addMany('Customers', [
    [
        'Code' => 'VLADIMIR',
        'Memo' => 'A Memo for Vladimir',
        // ...
    ],
    [
        'Code' => 'ALICE',
        'Memo' => 'A Memo for Alice',
        // ...
    ]
]);
```

You can also use the provided Model classes instead. These classes are named like the object models documented in the [Winbooks On Web documentation](https://help.winbooks.be/pages/viewpage.action?pageId=54529841).

```php
$alice = new Customer(['Code' => 'ALICE']);
$winbooks->addModel($alice);
```

### Updating data
```php
$winbooks->update('Customer', 'ALICE', [
    'Memo' => 'This is an updated memo for Alice',
]);

// Or multiple

$winbooks->updateMany('Customers', [
    [
        'Code' => 'VLADIMIR',
        'Memo' => 'This is an updated memo for Vladimir',
    ],
    [
        'Code' => 'ALICE',
        'Memo' => 'This is an updated memo for Alice',
    ]
]);
```

### Deleting data
```php
$winbooks->delete('Customer', 'VLADIMIR');
```

## Tests

This project uses [PEST](https://pestphp.com/) for tests.

To run the tests:
```
./vendor/bin/pest
```

with code coverage (needs xdebug)
```
./vendor/bin/pest --coverage
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
