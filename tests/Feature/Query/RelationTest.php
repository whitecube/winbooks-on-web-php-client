<?php

use Whitecube\Winbooks\Query\Relation;

it('can extract relation name from method name', function() {
    expect(Relation::extractRelationName('getFooRelation'))->toBe('foo');
    expect(Relation::extractRelationName('getFooBarRelation'))->toBe('fooBar');
    expect(Relation::extractRelationName())->toBeNull();
    expect(Relation::extractRelationName('setFooRelation'))->toBeNull();
    expect(Relation::extractRelationName('getFooAbcdefgh'))->toBeNull();
});
