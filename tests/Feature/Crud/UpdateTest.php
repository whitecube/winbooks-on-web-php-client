<?php

use Whitecube\Winbooks\Winbooks;
use Whitecube\Winbooks\Models\Customer;
use function Tests\test_folder;
use function Tests\cleanup;

beforeEach(function() {
    $this->winbooks = new Winbooks();
});


it('can update an existing customer', function() {
    test_folder();

    // Given we have an existing customer
    $alice = $this->winbooks->add(Customer::class, 'ALICE', [
        'MemoType' => '1',
        'Memo' => 'This is a memo for Alice Wilder',
        'Third' => [
            'Name' => 'Alice Wilder',
            'Website' => 'www.alice-wilder.com',
            'Code' => 'ALICE'
        ]
    ]);

    // We should be abe to update their data
    $this->winbooks->update(Customer::class, 'ALICE', [
        'Memo' => 'This is an updated memo for Alice Wilder',
    ]);

    $alice = $this->winbooks->get(Customer::class, 'ALICE');

    expect($alice->Memo)->toBe('This is an updated memo for Alice Wilder');

    cleanup(Customer::class, 'ALICE');
});


it('can update a list of customers', function() {
    test_folder();

    // Given we have a couple existing customers
    $alice = [
        'Code' => 'ALICE',
        'Third' => [
            'Name' => 'Alice Wilder',
            'Website' => 'www.alice-wilder.com',
            'Code' => 'ALICE'
        ]
    ];

    $john = [
        'Code' => 'JOHNDOE',
        'Third' => [
            'Name' => 'John Doe',
            'Website' => 'www.john-doe.com',
            'Code' => 'JOHNDOE'
        ]
    ];

    $this->winbooks->addMany(Customer::class, [$alice, $john]);

    // We should be able to update their data
    $alice['Third']['Name'] = 'Alice Wilder Updated';
    $john['Third']['Name'] = 'John Doe Updated';

    $this->winbooks->updateMany(Customer::class, [$alice, $john]);

    expect($this->winbooks->get(Customer::class, 'ALICE', 3)->Third->Name)->toBe('Alice Wilder Updated');
    expect($this->winbooks->get(Customer::class, 'JOHNDOE', 3)->Third->Name)->toBe('John Doe Updated');

    cleanup(Customer::class, 'ALICE', 'JOHNDOE');
});
