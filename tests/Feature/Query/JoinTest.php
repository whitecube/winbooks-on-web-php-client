<?php

use Whitecube\Winbooks\Query\Join;
use Whitecube\Winbooks\Query\Operator;
use Whitecube\Winbooks\Models\Customer;
use Whitecube\Winbooks\Models\Third;
use Whitecube\Winbooks\Exceptions\InvalidJoinException;

it('can create a join instance and guess the alias name', function() {
    $join = new Join(new Customer, new Third);

    expect($join->getAlias())->toBe('third');
    expect($join->isTargeting(new Customer))->toBeFalse();
    expect($join->isTargeting(new Third))->toBeTrue();
});

it('can serialize into a JSON array', function() {
    $join = new Join(new Customer, new Third);

    expect(json_decode(json_encode($join), true))->toMatchArray([
        'AliasName' => 'third',
        'Type' => 'Winbooks.TORM.OM.Third, Winbooks.TORM.OM'
    ]);
});

it('can overwrite the association alias', function() {
    $join = new Join(new Customer, new Third);

    expect($join->alias('foo'))->toBeInstanceOf(Join::class);

    expect(json_decode(json_encode($join), true))->toMatchArray([
        'AliasName' => 'foo'
    ]);
});

it('can define an owner table alias', function() {
    $join = new Join(new Customer, new Third);

    expect($join->owner('foo'))->toBeInstanceOf(Join::class);

    expect(json_decode(json_encode($join), true))->toMatchArray([
        'OwnerAlias' => 'foo'
    ]);
});

it('can define the association condition', function() {
    $join = new Join(new Customer, new Third);

    expect($join->on('foo','=','bar'))->toBeInstanceOf(Join::class);

    expect(json_decode(json_encode($join), true))->toMatchArray([
        'JoinType' => Operator::TYPE_EQPROPERTY,
        'LeftProperty' => 'Foo',
        'RightProperty' => 'Bar',
    ]);
});

it('can define the association condition without operator', function() {
    $join = new Join(new Customer, new Third);

    expect($join->on('foo','bar'))->toBeInstanceOf(Join::class);

    expect(json_decode(json_encode($join), true))->toMatchArray([
        'JoinType' => Operator::TYPE_EQPROPERTY,
        'LeftProperty' => 'Foo',
        'RightProperty' => 'Bar',
    ]);
});

it('can define the association condition with "using" alias method', function() {
    $join = new Join(new Customer, new Third);

    expect($join->using('foo','bar'))->toBeInstanceOf(Join::class);

    expect(json_decode(json_encode($join), true))->toMatchArray([
        'JoinType' => Operator::TYPE_EQPROPERTY,
        'LeftProperty' => 'Foo',
        'RightProperty' => 'Bar',
    ]);
});

it('can check that join can\'t be used without alias', function() {
    $join = (new Join(new Customer, new Third))
        ->on('Foo','=','Bar')
        ->alias('');

    $join->failIfNotUsable();
})->throws(InvalidJoinException::class);

it('can check that join can\'t be used without condition', function() {
    $join = new Join(new Customer, new Third);

    $join->failIfNotUsable();
})->throws(InvalidJoinException::class);
