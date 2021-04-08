<?php

use Whitecube\Winbooks\Query;
use Whitecube\Winbooks\Query\Join;
use Whitecube\Winbooks\Query\Operator;
use Whitecube\Winbooks\Models\Third;
use Whitecube\Winbooks\Models\Customer;
use Whitecube\Winbooks\Models\Logistics\DocumentHeader;
use Whitecube\Winbooks\Exceptions\InvalidJoinException;

it('can construct with object model and optional alias', function() {
    $withAlias = new Query(new Customer, 'foo');
    $withoutAlias = new Query(new Customer);

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
    $query = new Query(new Customer);

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
    $query = new Query(new Customer);

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
    $query = (new Query(new Customer))->select('foo','bar');

    expect($query->select(null))->toBeInstanceOf(Query::class);

    $query = json_decode(json_encode($query), true);

    expect($query)->not->toHaveKey('ProjectionsList');
});

it('can add where condition using 2 parameters', function() {
    $query = new Query(new Customer);

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
    $query = new Query(new Customer);

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
    (new Query(new Customer))->where('foo');
})->throws(\InvalidArgumentException::class);

it('can add a "property comparison" where condition when providing a property as value', function() {
    $query = new Query(new Customer);

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
    $query = new Query(new Customer);

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
    $query = new Query(new Customer);

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

it('can add multiple ordering clauses', function() {
    $query = new Query(new Customer);

    $query->orderBy('foo')->orderBy('bar', 'desc');

    $query = json_decode(json_encode($query), true);

    expect($query)->toMatchArray([
        'Orders' => [
            [
                'PropertyName' => 'Foo',
                'Alias' => null,
                'Projections' => null,
                'Ascending' => true,
            ],
            [
                'PropertyName' => 'Bar',
                'Alias' => null,
                'Projections' => null,
                'Ascending' => false,
            ],
        ]
    ]);
});

it('can remove ordering clauses', function() {
    $query = new Query(new Customer);

    $query->orderBy('foo')->orderBy('bar', 'desc');

    $query->orderBy();

    $query = json_decode(json_encode($query), true);

    expect($query)->not->toHaveKey('Orders');
});

it('can add a join instance to the associations', function() {
    $query = new Query(new Customer);

    $join = (new Join(new Customer, new Third))
        ->on('Foo','=','Bar')
        ->alias('fooAlias')
        ->owner('fooOwner');


    expect($query->associate($join, function($join) {
        $join->on('baz','=','test')->alias('bazAlias')->owner('bazOwner');
    }))->toBeInstanceOf(Query::class);

    $query = json_decode(json_encode($query), true);

    expect($query)->toMatchArray([
        'Association' => [
            'bazAlias' => [
                'OwnerAlias' => 'bazOwner',
                'AliasName' => 'bazAlias',
                'Type' => 'Winbooks.TORM.OM.Third, Winbooks.TORM.OM',
                'JoinType' => Operator::TYPE_EQPROPERTY,
                'LeftProperty' => 'Baz',
                'RightProperty' => 'Test',
            ]
        ]
    ]);
});

it('cannot add an unconfigured join instance to the associations', function() {
    $query = new Query(new Customer);

    $join = new Join(new Customer, new Third);

    $query->associate($join);
})->throws(InvalidJoinException::class);

it('can add an association using Query::join method', function() {
    $target = new DocumentHeader();

    $cases = [
        $target,
        $target->getType(),
        get_class($target),
        $target->getOM(),
        $target->getOMS()
    ];

    foreach ($cases as $model) {
        $query = new Query(new Customer);

        $query->join($model, function($join) {
            $join->on('Foo','Bar')->alias('foo');
        });

        expect($join = json_decode(json_encode($query), true)['Association']['foo'] ?? null)->toBeArray();
        expect($join)->not->toHaveKey('OwnerAlias');
        expect($join)->toHaveKey('AliasName');
        expect($join)->toHaveKey('Type');
        expect($join)->toHaveKey('JoinType');
        expect($join)->toHaveKey('LeftProperty');
        expect($join)->toHaveKey('RightProperty');
    }
});

it('can add an association using Query::join method and use an existing relation preconfiguration', function() {
    $query = new Query(new Customer);

    expect($query->join(new Third))->toBeInstanceOf(Query::class);

    expect($join = json_decode(json_encode($query), true)['Association']['third'] ?? null)->toBeArray();
    expect($join)->not->toHaveKey('OwnerAlias');
    expect($join)->toHaveKey('AliasName');
    expect($join)->toHaveKey('Type');
    expect($join)->toHaveKey('JoinType');
    expect($join)->toHaveKey('LeftProperty');
    expect($join)->toHaveKey('RightProperty');
});

it('can add an association using Query::with method and use its existing relation preconfiguration', function() {
    $query = new Query(new Customer);

    expect($query->with('third', function($join) {
        $join->alias('foo')->owner('baz');
    }))->toBeInstanceOf(Query::class);

    expect($join = json_decode(json_encode($query), true)['Association']['foo'] ?? null)->toBeArray();
    expect($join)->toHaveKey('OwnerAlias');
    expect($join)->toHaveKey('AliasName');
    expect($join)->toHaveKey('Type');
    expect($join)->toHaveKey('JoinType');
    expect($join)->toHaveKey('LeftProperty');
    expect($join)->toHaveKey('RightProperty');
});
