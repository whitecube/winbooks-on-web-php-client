<?php

use Whitecube\Winbooks\Query;
use Whitecube\Winbooks\Models\Customer;
use Whitecube\Winbooks\Exceptions\UndefinedOperatorException;

it('can recognize operator codes when parsing winbooks operators', function() {
    expect(Query::operator('0'))->toBe(Query::OPERATOR_EQ);
    expect(Query::operator(Query::OPERATOR_EQ))->toBe(Query::OPERATOR_EQ);
});

it('can transform symbolic operators into winbooks operators', function() {
    expect(Query::operator('='))->toBe(Query::OPERATOR_EQ);
    expect(Query::operator('=='))->toBe(Query::OPERATOR_EQ);
    expect(Query::operator('>='))->toBe(Query::OPERATOR_GE);
    expect(Query::operator('>'))->toBe(Query::OPERATOR_GT);
    expect(Query::operator('<='))->toBe(Query::OPERATOR_LE);
    expect(Query::operator('<'))->toBe(Query::OPERATOR_LT);
});

it('can transform constant names into winbooks operators', function() {
    expect(Query::operator('LIKE'))->toBe(Query::OPERATOR_LIKE);
    expect(Query::operator('is not null'))->toBe(Query::OPERATOR_ISNOTNULL);
});

it('throws an exception when converting a non-string operator', function() {
    Query::operator(null);
})->throws(UndefinedOperatorException::class);

it('throws an exception when unable to convert string operator', function() {
    Query::operator('something undefined');
})->throws(UndefinedOperatorException::class);

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
            ['PropertyName' => 'foo', 'Operator' => Query::OPERATOR_SELECT],
            ['PropertyName' => 'test', 'Operator' => Query::OPERATOR_SELECT],
            ['PropertyName' => 'baz', 'Operator' => Query::OPERATOR_SELECT],
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
            ['PropertyName' => 'foo', 'Operator' => Query::OPERATOR_AVG],
            ['PropertyName' => 'bar', 'Operator' => Query::OPERATOR_AVG],
            ['PropertyName' => 'test', 'Operator' => Query::OPERATOR_SELECT],
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
                'Operator' => Query::OPERATOR_EQ,
                'PropertyName' => 'foo',
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
                'Operator' => Query::OPERATOR_GT,
                'PropertyName' => 'foo',
                'OtherPropertyName' => '',
                'Values' => [10]
            ],
        ]
    ]);
});

it('throws an exception when providing too few parameters to where condition', function() {
    (new Query(new Customer()))->where('foo');
})->throws(\InvalidArgumentException::class);
