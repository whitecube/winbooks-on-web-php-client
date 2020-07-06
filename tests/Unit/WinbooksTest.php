<?php

use Winbooks\Winbooks;

beforeEach(function() {
    $this->winbooks = new Winbooks('john@doe.test', 'foo-token');
});

it('can create a winbooks instance', function() {
    assertInstanceOf(Winbooks::class, $this->winbooks);
});

it('throws exception if token is missing', function() {
    new Winbooks('');
})->throws(\Winbooks\Exceptions\TokenRequiredException::class);

it('can get all customers from a specific folder', function() {
    $this->winbooks->folder('PARFIWEB_DEMO')->all('Customers');
});

it('can get a customer by code', function() {
    $this->winbooks->folder('PARFIWEB_DEMO')->get('Customer', 'VLADIMIR');
});
