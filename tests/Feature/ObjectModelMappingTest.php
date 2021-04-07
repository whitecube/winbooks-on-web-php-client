<?php

use Whitecube\Winbooks\Winbooks;
use Whitecube\Winbooks\Models\Logistics\StockTransaction;

it('can recognize defined object model types', function() {
    expect(Winbooks::isModelType(null))->toBeFalse();
    expect(Winbooks::isModelType(true))->toBeFalse();
    expect(Winbooks::isModelType(new \stdClass))->toBeFalse();
    expect(Winbooks::isModelType([]))->toBeFalse();
    expect(Winbooks::isModelType(42))->toBeFalse();
    expect(Winbooks::isModelType('foo'))->toBeFalse();
    expect(Winbooks::isModelType('Winbooks.TORM.OM.Logistics.StockTransaction, Winbooks.TORM.OM'))->toBeTrue();
});

it('throws an exception when making an undefined object model', function() {
    Winbooks::makeModelForType('foo');
})->throws(\Whitecube\Winbooks\Exceptions\UndefinedObjectModelException::class);

it('can instanciate an object model based on a given type', function() {
    $model = Winbooks::makeModelForType('Winbooks.TORM.OM.Logistics.StockTransaction, Winbooks.TORM.OM');

    expect($model)->toBeInstanceOf(StockTransaction::class);
});

it('cannot transform other datatypes than arrays to models', function() {
    expect(Winbooks::toModel(null))->toBeNull();
    expect(Winbooks::toModel(true))->toBeTrue();
    expect(Winbooks::toModel(123))->toBeInt();
    expect(Winbooks::toModel(new StockTransaction))->toBeInstanceOf(StockTransaction::class);
});

it('cannot transform arrays with wrong $type attributes', function() {
    $withoutType = ['foo' => 'bar'];
    $withUndefinedType = ['$type' => 'foobaz','foo' => 'bar'];

    expect(Winbooks::toModel($withoutType))->toBeArray();
    expect(Winbooks::toModel($withUndefinedType))->toBeArray();
});

it('can transform arrays with defined $type attribute', function() {
    $data = [
        '$type' => 'Winbooks.TORM.OM.Logistics.StockTransaction, Winbooks.TORM.OM',
        'foo' => 'bar'
    ];

    expect(Winbooks::toModel($data))->toBeInstanceOf(StockTransaction::class);
});
