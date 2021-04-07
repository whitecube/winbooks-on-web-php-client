<?php

use Whitecube\Winbooks\Winbooks;
use Whitecube\Winbooks\Collection;
use Whitecube\Winbooks\Models\Third;
use Whitecube\Winbooks\Models\Customer;
use Whitecube\Winbooks\Models\ThirdCivility;
use function Tests\test_folder;
use function Tests\authenticate;

beforeEach(function() {
    $this->winbooks = new Winbooks();
});


it('can get all customers from a specific folder', function() {
    test_folder();
    $data = $this->winbooks->all('Customers');

    expect($data)->toBeInstanceOf(Collection::class);
    expect($data)->toBeIterable();
    expect($data->count())->toBeGreaterThan(1);
    expect(count($data))->toBe($data->count());
    expect($data[0])->toBeInstanceOf(Customer::class);
    expect($data->first())->toBeInstanceOf(Customer::class);
    expect($data->last())->toBeInstanceOf(Customer::class);

});

it('can get a customer by code', function() {
    test_folder();
    $customer = $this->winbooks->get('Customer', 'ARTHUR');

    expect($customer)->toBeInstanceOf(Customer::class);
    expect($customer->getCode())->toBe('ARTHUR');
});

it('cannot get nested data from level 1 requests', function() {
    test_folder();

    $customer = $this->winbooks->get('Customer', 'ARTHUR');

    expect($customer->Third)->toBeNull();
});

it('can get nested data from level 2 requests', function() {
    test_folder();

    $customer = $this->winbooks->get('Customer', 'ARTHUR', 2);

    expect($customer->Third)->toBeInstanceOf(Third::class);
});

it('can get nested data merged from aggregated level 3 requests', function() {
    test_folder();

    $customer = $this->winbooks->get('Customer', 'ARTHUR', 3);

    expect($customer->Third->Civility)->toBeInstanceOf(ThirdCivility::class);

    // The following test depends on the testing folder's state... which contained
    // nearly 2000 entries when this test was written.
    // API responses with more than 101 objects are chunked, this test aims
    // to check if the API wrapper executes multiple API calls until the object
    // contains all its promised data.
    expect(count($customer->Vat->GLTransactions))->toBeGreaterThan(101);
});
