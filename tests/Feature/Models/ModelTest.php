<?php

use Whitecube\Winbooks\Winbooks;
use Whitecube\Winbooks\Models\Customer;
use function Tests\authenticate;


test('a model can accept properties dynamically', function() {
    $customer = new Customer();
    $customer->foobar = 'baz';
    assertSame('baz', $customer->foobar);
});


test('a model can be serialized into the correct JSON structure', function() {
    $customer = new Customer();
    $customer->foobar = 'baz';
    $encoded = json_encode($customer);
    assertStringContainsString('"foobar":"baz"', $encoded);
    assertStringContainsString('"$type":"Winbooks.TORM.OM.Customer, Winbooks.TORM.OM"', $encoded);
});


test('a model can accept values in its constructor', function() {
    $customer = new Customer(['foo' => 'bar']);
    assertSame('bar', $customer->foo);
});


test('a model can return its Code or its Id as a fallback', function() {
    $alice = new Customer(['Code' => 'ALICE']);
    $john = new Customer(['Id' => '1234']);
    $jane = new Customer();

    assertSame('ALICE', $alice->getCode());
    assertSame('1234', $john->getCode());
    assertNull($jane->getCode());
});

