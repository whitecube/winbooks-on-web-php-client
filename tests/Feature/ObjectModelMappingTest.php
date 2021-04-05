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

it('cannot transform other datatypes than stdClasses to models', function() {
    $winbooks = new Winbooks();

    expect($winbooks->toModel(null))->toBeNull();
    expect($winbooks->toModel(true))->toBeTrue();
    expect($winbooks->toModel([]))->toBeArray();
    expect($winbooks->toModel(new StockTransaction))->toBeInstanceOf(StockTransaction::class);
});

it('cannot transform stdClasses with wrong $type attributes', function() {
    $winbooks = new Winbooks();

    $withoutType = new \stdClass();
    $withoutType->foo = 'bar';

    $withUndefinedType = new \stdClass();
    $withUndefinedType->{'$type'} = 'foo';
    $withUndefinedType->foo = 'bar';

    expect($winbooks->toModel($withoutType))->toBeInstanceOf(\stdClass::class);
    expect($winbooks->toModel($withUndefinedType))->toBeInstanceOf(\stdClass::class);
});

it('can transform stdClasses with defined $type attribute', function() {
    $winbooks = new Winbooks();

    $data = new \stdClass();
    $data->{'$type'} = 'Winbooks.TORM.OM.Logistics.StockTransaction, Winbooks.TORM.OM';
    $data->foo = 'bar';

    expect($winbooks->toModel($data))->toBeInstanceOf(StockTransaction::class);
});
