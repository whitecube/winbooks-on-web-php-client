<?php

use Whitecube\Winbooks\Query;
use Whitecube\Winbooks\Query\Operator;
use Whitecube\Winbooks\Exceptions\UndefinedOperatorException;

it('can create operator instance from winbooks operator constant', function() {
    $equals = new Operator('0');
    $greaterThan = new Operator(Operator::TYPE_GT);

    expect(json_decode(json_encode($equals)))->toBe(Operator::TYPE_EQ);
    expect(json_decode(json_encode($greaterThan)))->toBe(Operator::TYPE_GT);
});

it('can create operator from symbolic alternative', function() {
    $cases = [
        '=' => Operator::TYPE_EQ,
        '==' => Operator::TYPE_EQ,
        '>=' => Operator::TYPE_GE,
        '>' => Operator::TYPE_GT,
        '<=' => Operator::TYPE_LE,
        '<' => Operator::TYPE_LT,
    ];

    foreach ($cases as $symbol => $code) {
        expect(json_decode(json_encode(new Operator($symbol))))->toBe($code);
    }
});

it('can create operator from constant name static method', function() {
    $select = Operator::select();
    $isNotNull = Operator::isNotNull();

    expect(json_decode(json_encode($select)))->toBe(Operator::TYPE_SELECT);
    expect(json_decode(json_encode($isNotNull)))->toBe(Operator::TYPE_ISNOTNULL);
});

it('can create operator from Query::operator method', function() {
    $like = Query::operator('LIKE');
    $isNotNull = Query::operator('is not null');
    $select = Query::operator(new Operator(Operator::TYPE_SELECT));

    expect(json_decode(json_encode($like)))->toBe(Operator::TYPE_LIKE);
    expect(json_decode(json_encode($isNotNull)))->toBe(Operator::TYPE_ISNOTNULL);
    expect(json_decode(json_encode($select)))->toBe(Operator::TYPE_SELECT);
});

it('throws an exception when converting a non-string operator', function() {
    new Operator(null);
})->throws(UndefinedOperatorException::class);

it('throws an exception when unable to convert string operator', function() {
    new Operator('something undefined');
})->throws(UndefinedOperatorException::class);
