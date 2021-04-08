<?php

use Whitecube\Winbooks\Query;
use Whitecube\Winbooks\Exceptions\UndefinedOperatorException;

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

it('throws exception when unable to convert operator', function() {
    Query::operator('something undefined');
})->throws(UndefinedOperatorException::class);