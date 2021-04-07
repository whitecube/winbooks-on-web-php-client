<?php

use Whitecube\Winbooks\Winbooks;
use Whitecube\Winbooks\Models\Customer;
use function Tests\authenticate;

test('a model can accept values in its constructor', function() {
    $customer = new Customer(['Foo' => 'bar']);

    expect($customer->Foo)->toBe('bar');
    expect($customer->getAttributes())->toBeArray();
});

test('a model can set properties methodologically', function() {
    $customer = new Customer();
    $customer->set('foo', 'bar');

    $attributes = $customer->getAttributes();

    // "set" should automatically capitalize the attribute's first char
    expect($attributes['Foo'] ?? null)->toBe('bar');
});

test('a model can set properties dynamically', function() {
    $customer = new Customer();
    $customer->foo = 'bar';

    $attributes = $customer->getAttributes();

    // "set" should automatically capitalize the attribute's first char
    expect($attributes['Foo'] ?? null)->toBe('bar');
});

test('a model can set properties as an array', function() {
    $customer = new Customer();
    $customer['foo'] = 'bar';

    $attributes = $customer->getAttributes();

    // "set" should automatically capitalize the attribute's first char
    expect($attributes['Foo'] ?? null)->toBe('bar');
});

test('a model can return properties methodologically', function() {
    $customer = new Customer(['Foo' => 'bar']);

    // "get" should automatically capitalize the attribute's first char
    expect($customer->get('foo'))->toBe('bar');
});

test('a model can return properties dynamically', function() {
    $customer = new Customer(['Foo' => 'bar']);

    // "get" should automatically capitalize the attribute's first char
    expect($customer->foo)->toBe('bar');
});

test('a model can return properties as an array', function() {
    $customer = new Customer(['Foo' => 'bar']);

    // "get" should automatically capitalize the attribute's first char
    expect($customer['foo'])->toBe('bar');
});

test('a model can check property existence', function() {
    $customer = new Customer(['Foo' => 'bar']);

    expect($customer->has('bar'))->toBeFalse();
    expect($customer->has('Foo'))->toBeTrue();
    // "has" should automatically capitalize the attribute's first char
    expect($customer->has('foo'))->toBeTrue();
});

test('a model can remove properties methodologically', function() {
    $customer = new Customer(['Foo' => 'bar', 'Baz' => 'test']);

    // "remove" should automatically capitalize the attribute's first char
    $customer->remove('baz');

    expect($customer->has('baz'))->toBeFalse();
    expect($customer->has('foo'))->toBeTrue();
});

test('a model can remove properties as an array', function() {
    $customer = new Customer(['Foo' => 'bar', 'Baz' => 'test']);

    // "remove" should automatically capitalize the attribute's first char
    unset($customer['baz']);

    expect($customer->has('baz'))->toBeFalse();
    expect($customer->has('foo'))->toBeTrue();
});

test('a model can be serialized into the correct JSON structure', function() {
    $customer = new Customer(['Foo' => 'bar']);
    $encoded = json_encode($customer);

    $this->assertStringContainsString('"Foo":"bar"', $encoded);
    $this->assertStringContainsString('"$type":"Winbooks.TORM.OM.Customer, Winbooks.TORM.OM"', $encoded);
});

test('a model can return its Code or its Id as a fallback', function() {
    $alice = new Customer(['Code' => 'ALICE']);
    $john = new Customer(['Id' => '1234']);
    $jane = new Customer();

    expect($alice->getCode())->toBe('ALICE');
    expect($john->getCode())->toBe('1234');
    expect($jane->getCode())->toBeNull();
});

