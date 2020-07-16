<?php

use Winbooks\Winbooks;
use function Tests\test_folder;
use function Tests\cleanup;

beforeEach(function() {
    $this->winbooks = new Winbooks();
});


it('can update an existing customer', function() {
    test_folder();

    // Given we have an existing customer
    $alice = $this->winbooks->add('Customer', 'ALICE', [
        'MemoType' => '1',
        'Memo' => 'This is a memo for Alice Wilder',
        'Third' => [
            'Name' => 'Alice Wilder',
            'Website' => 'www.alice-wilder.com',
            'Code' => 'ALICE'
        ]
    ]);

    // We should be abe to update their data
    $this->winbooks->update('Customer', 'ALICE', [
        'Memo' => 'This is an updated memo for Alice Wilder',
    ]);

    $alice = $this->winbooks->get('Customer', 'ALICE');
    assertSame('This is an updated memo for Alice Wilder', $alice->Memo);

    cleanup('Customer', 'ALICE');
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

    $this->winbooks->addMany('Customers', [$alice, $john]);

    // We should be able to update their data
    $alice['Third']['Name'] = 'Alice Wilder Updated';
    $john['Third']['Name'] = 'John Doe Updated';

    $this->winbooks->updateMany('Customers', [$alice, $john]);

    assertSame('Alice Wilder Updated', $this->winbooks->get('Customer', 'ALICE', 3)->Third->Name);
    assertSame('John Doe Updated', $this->winbooks->get('Customer', 'JOHNDOE', 3)->Third->Name);

    cleanup('Customer', 'ALICE', 'JOHNDOE');
});
