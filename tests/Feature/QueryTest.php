<?php

use Whitecube\Winbooks\Query;
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