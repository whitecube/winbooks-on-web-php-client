<?php

use Whitecube\Winbooks\Winbooks;
use Whitecube\Winbooks\Models\Customer;
use function Tests\test_folder;

beforeEach(function() {
    $this->winbooks = new Winbooks();
});

it('can delete a customer', function() {
    test_folder();
    // make a temporary customer
    $code = 'DELETE_TEST';
    $this->winbooks->add(Customer::class, $code, [
        'Third' => [
            'Name' => 'Customer deletion test',
            'Code' => $code
        ]
    ]);

    expect($this->winbooks->get(Customer::class, $code))->not->toBeNull();

    // delete it and check that it's gone
    $this->winbooks->delete(Customer::class, $code);

    expect($this->winbooks->get(Customer::class, $code))->toBeNull();
});
