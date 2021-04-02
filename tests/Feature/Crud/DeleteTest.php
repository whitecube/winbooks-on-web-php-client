<?php

use Whitecube\Winbooks\Winbooks;
use function Tests\test_folder;

beforeEach(function() {
    $this->winbooks = new Winbooks();
});

it('can delete a customer', function() {
    test_folder();
    // make a temporary customer
    $code = 'DELETE_TEST';
    $this->winbooks->add('Customer', $code, [
        'Third' => [
            'Name' => 'Customer deletion test',
            'Code' => $code
        ]
    ]);

    expect($this->winbooks->get('Customer', $code))->not->toBeNull();

    // delete it and check that it's gone
    $this->winbooks->delete('Customer', $code);

    expect($this->winbooks->get('Customer', $code))->toBeNull();
});
