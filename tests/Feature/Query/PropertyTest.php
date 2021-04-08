<?php

use Whitecube\Winbooks\Query;
use Whitecube\Winbooks\Query\Property;

it('can create single property from string', function() {
    $property = new Property('Foo');

    expect(json_decode(json_encode($property)))->toBe('Foo');
});

it('can create compound property from string', function() {
    $property = new Property('something.Foo');

    expect(json_decode(json_encode($property)))->toBe('something.Foo');
});

it('can create property from Query::property method', function() {
    $string = Query::property('Foo');
    $instance = Query::property(new Property('Foo'));

    expect(json_decode(json_encode($string)))->toBe('Foo');
    expect(json_decode(json_encode($instance)))->toBe('Foo');
});

it('can remove unecessary chars', function() {
    $property = new Property('. .something. .Foo. . ');

    expect(json_decode(json_encode($property)))->toBe('something.Foo');
});

it('can capitalize first char of property name', function() {
    $property = new Property('something.foo');

    expect(json_decode(json_encode($property)))->toBe('something.Foo');
});

it('throws an exception when nothing usable is found', function() {
    new Property(' . . .');
})->throws(\InvalidArgumentException::class);