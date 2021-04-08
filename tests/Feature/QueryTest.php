<?php

use Whitecube\Winbooks\Query;
use Whitecube\Winbooks\Query\Operator;
use Whitecube\Winbooks\Models\Customer;

it('can construct with object model and optional alias', function() {
    $withAlias = new Query(new Customer(), 'foo');
    $withoutAlias = new Query(new Customer());

    $withAlias = json_decode(json_encode($withAlias), true);
    $withoutAlias = json_decode(json_encode($withoutAlias), true);

    expect($withAlias)->toMatchArray([
        'EntityType' => 'Winbooks.TORM.OM.Customer, Winbooks.TORM.OM',
        'Alias' => 'foo'
    ]);

    expect($withoutAlias)->toMatchArray([
        'EntityType' => 'Winbooks.TORM.OM.Customer, Winbooks.TORM.OM',
        'Alias' => 'this'
    ]);
});

it('can add properties to the query\'s projectionsList', function() {
    $query = new Query(new Customer());

    expect($query->select('foo'))->toBeInstanceOf(Query::class);

    $query->select('test','baz');

    $query = json_decode(json_encode($query), true);

    expect($query)->toMatchArray([
        'ProjectionsList' => [
            ['PropertyName' => 'Foo', 'Operator' => Operator::TYPE_SELECT],
            ['PropertyName' => 'Test', 'Operator' => Operator::TYPE_SELECT],
            ['PropertyName' => 'Baz', 'Operator' => Operator::TYPE_SELECT],
        ]
    ]);
});

it('can add properties to the query\'s projectionsList using custom operator', function() {
    $query = new Query(new Customer());

    expect($query->selectOperator('AVG','foo','bar'))->toBeInstanceOf(Query::class);

    $query->select('test');

    $query = json_decode(json_encode($query), true);

    expect($query)->toMatchArray([
        'ProjectionsList' => [
            ['PropertyName' => 'Foo', 'Operator' => Operator::TYPE_AVG],
            ['PropertyName' => 'Bar', 'Operator' => Operator::TYPE_AVG],
            ['PropertyName' => 'Test', 'Operator' => Operator::TYPE_SELECT],
        ]
    ]);
});

it('can empty projectionsList', function() {
    $query = (new Query(new Customer()))->select('foo','bar');

    expect($query->select(null))->toBeInstanceOf(Query::class);

    $query = json_decode(json_encode($query), true);

    expect($query)->not->toHaveKey('ProjectionsList');
});

it('can add where condition using 2 parameters', function() {
    $query = new Query(new Customer());

    expect($query->where('foo','bar'))->toBeInstanceOf(Query::class);

    $query = json_decode(json_encode($query), true);

    expect($query)->toMatchArray([
        'Conditions' => [
            [
                'Operator' => Operator::TYPE_EQ,
                'PropertyName' => 'Foo',
                'OtherPropertyName' => '',
                'Values' => ['bar']
            ],
        ]
    ]);
});

it('can add where condition using 3 parameters', function() {
    $query = new Query(new Customer());

    expect($query->where('foo','>',10))->toBeInstanceOf(Query::class);

    $query = json_decode(json_encode($query), true);

    expect($query)->toMatchArray([
        'Conditions' => [
            [
                'Operator' => Operator::TYPE_GT,
                'PropertyName' => 'Foo',
                'OtherPropertyName' => '',
                'Values' => [10]
            ],
        ]
    ]);
});

it('throws an exception when providing too few parameters to where condition', function() {
    (new Query(new Customer()))->where('foo');
})->throws(\InvalidArgumentException::class);

it('can add a "property comparison" where condition when providing a property as value', function() {
    $query = new Query(new Customer());

    $query->where('foo','>',Query::property('bar'));

    $query = json_decode(json_encode($query), true);

    expect($query)->toMatchArray([
        'Conditions' => [
            [
                'Operator' => Operator::TYPE_GTPROPERTY,
                'PropertyName' => 'Foo',
                'OtherPropertyName' => 'Bar',
                'Values' => []
            ],
        ]
    ]);
});

it('can add a default ascending order by clause', function() {
    $query = new Query(new Customer());

    expect($query->orderBy('foo'))->toBeInstanceOf(Query::class);

    $query = json_decode(json_encode($query), true);

    expect($query)->toMatchArray([
        'Orders' => [
            [
                'PropertyName' => 'Foo',
                'Alias' => null,
                'Projections' => null,
                'Ascending' => true,
            ],
        ]
    ]);
});

it('can add a descending order by clause', function() {
    $query = new Query(new Customer());

    expect($query->orderBy('foo', 'desc'))->toBeInstanceOf(Query::class);

    $query = json_decode(json_encode($query), true);

    expect($query)->toMatchArray([
        'Orders' => [
            [
                'PropertyName' => 'Foo',
                'Alias' => null,
                'Projections' => null,
                'Ascending' => false,
            ],
        ]
    ]);
});
