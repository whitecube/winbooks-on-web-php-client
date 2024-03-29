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
use Whitecube\Winbooks\Winbooks;

// $access_token and $refresh_token can be null if you do not have them yet
$winbooks = new Winbooks($access_token, $refresh_token);

if(! $winbooks->authenticated()) {
    [$access_token, $refresh_token] = $winbooks->authenticate($email, $exchange_token);
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

All getter methods will return the JSON data directly from WoW, already decoded and wrapped into Object Model instances when possible.

#### Returning all data

To get all results from an object model, use the `all($object_model, $max_level = 1)` method.

```php
$customers = $winbooks->all('Customers');
```

> **Warning**: Depending on the size of your dataset and the server's memory limit, `all()` can cause critical server errors since its results are not paginated. The API wrapper will continue fetching objects until Winbook's REST API indicates everything has been transferred. This is quite a big issue, documented in [Winbook's documentation](https://help.winbooks.be/display/DEV/1.+Query+Data#id-1.QueryData-E.Chunkingdata), which will not be fixed in this package until Winbook's REST API will implement proper pagination options.
> If you need pagination, it is preferable to [use queries](#querying-data).

#### Returning data for a single object model

To get a single result from an object model, use the `get($object_model, $code, $max_level = 1)` method.

> **Note**: you can substitute $code for the ID if you have it.

```php
$vlad = $winbooks->get('Customer', 'VLADIMIR');
// With ID
$vlad = $winbooks->get('Customer', '4713a22f-ebc0-ea11-80c7-0050s68cc4a2');
```

To specify the amount of nested data you want ([`maxLevel` parameter](https://help.winbooks.be/display/DEV/1.+Query+Data)), you can pass it as a third param to the get method.

```php
$vlad = $winbooks->get('Customer', 'VLADIMIR', 3);
```

#### Querying data

Listing object models is often more complicated than just fetching all results. To get more refined results, it is recommended to use the Query Builder provided in this package. It will make API interactions more precise and it is therefore a great way to enhance performance.

Queries can be send using the `query($object_model, $query_builder, $max_level = 1)` method.

```php
$results = $winbooks->query('Customers', function($query) {
    // Build your query...
    $query->select('Id', 'Code')->orderBy('Created', 'desc')->paginate(20);
});
```

##### Select (Projection Lists)

To only project a few properties instead of full object models, it is recommended to use the `select(...$properties)` method:

```php
$query->select('Id', 'VatApplicable', 'Memo');
```

In order to perform a specific kind of select, use the `selectOperator($operator, ...$properties)` method:

```php
use Whitecube\Winbooks\Query\Operator;

$query->selectOperator(Operator::distinct(), 'Id', 'VatApplicable', 'Memo');
```

##### Where (Conditions)

Simple `=` conditions can be applied as follows:

```php
$query->where('Id', '4713a22f-ebc0-ea11-80c7-0050s68cc4a2');
```

For other comparison methods, use the common `>`, `>=`, `<` & `<=` symbols:

```php
$query->where('Amount', '>=', 1000.00);
```

Or even more sofisticated operators (here's the [full list of available operators](https://github.com/whitecube/winbooks-on-web-php-client/blob/master/src/Query/Operator.php)):

```php
use Whitecube\Winbooks\Query\Operator;

$query->where('Name', 'like', '%Vlad%');
// or
$query->where('Name', Operator::having(), 'something');
```

Sometimes it is necessary to compare object model properties:

```php
use Whitecube\Winbooks\Query;

$query->where('Code', '=', Query::property('Id'));
```

##### Order By

To get the results sorted in a certain way, use the `orderBy($property, $direction)` method.

> **Note**: It is possible to chain multiple `orderBy()` calls in order to define more fine-grained results sorting.

```php
$query->orderBy('Amount', 'desc');
```

##### Relations & Joins (Associations)

Most object models have their associations with sub-models defined in this package, making it easy to query relations with associated object models. Feel free to open a PR if we missed some of them.

```php
$query->with('third');
```

The `with($relation, $configurator)` method allows to overwrite the default relation configuration by providing a callback function as second parameter:

```php
$query->with('third', function($join) {
    $join->on('Third_Id', 'Id')->owner('something')->alias('person');
});
```

For more advanced or unavailable relations, it is also possible to associate data using your own joins:

```php
use Whitecube\Winbooks\Models\Logistics\DocumentHeader;

$query->join(DocumentHeader::class, function($join) {
    $join->on('DocumentHeader_Id', '=', 'Some_Property')->alias('header');
});
```

##### Limiting results & Pagination

Limiting the amount of queried results is often necessary in order to avoid endless requests. Just use the `take($amount)` method in order to limit the results to the desired amount:

```php
$query->take(50);
```

In order to take the next results, you should first `skip($amount)` the previous results:

```php
$query->skip(50);
```

This basically is pagination, so we also added a shorthand method that combines both concepts in a more comprehensive straightforward way using the `paginate($perPage, $page)` method:

```php
// Only take 50 results to display on the first page
$query->paginate(50);
// Query the 50 next results (on page 2)
$query->paginate(50, 2);
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
use Whitecube\Winbooks\Models\Customer;

$vlad = new Customer(['Code' => 'VLADIMIR']);
$alice = new Customer(['Code' => 'ALICE']);

$winbooks->addModel($vlad);
// or multiple
$winbooks->addModels([$vlad, $alice]);
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

First, create a `.env` file from the `.env.example` and fill it with your API testing credentials.

To run the tests:
```
./vendor/bin/pest
```

with code coverage (needs pcov or xdebug)
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
