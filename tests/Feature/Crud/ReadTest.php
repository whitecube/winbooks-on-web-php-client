<?php

use Whitecube\Winbooks\Winbooks;
use Whitecube\Winbooks\Models\Customer;
use function Tests\test_folder;
use function Tests\authenticate;

beforeEach(function() {
    $this->winbooks = new Winbooks();
});


it('can get all customers from a specific folder', function() {
    authenticate();
    $data = $this->winbooks->folder('PARFIWEB_DEMO')->all('Customers');

    expect($data)->toBeArray();
    expect($data[0] ?? null)->toBeInstanceOf(Customer::class);
});


it('can get a customer by code', function() {
    test_folder();
    $customer = $this->winbooks->get('Customer', 'ARTHUR');

    expect($customer)->toBeInstanceOf(Customer::class);
    expect($customer->getCode())->toBe('ARTHUR');
});


it('can get varying amounts of nested data', function() {
    test_folder();

    $first = $this->winbooks->get('Customer', 'ARTHUR');
    $second = $this->winbooks->get('Customer', 'ARTHUR', 2);
    $third = $this->winbooks->get('Customer', 'ARTHUR', 3);

    expect($first->Third)->toBeNull();
    expect($second->Third)->not->toBeNull();
    expect($third->Third)->toHaveProperty('Civility');
});
