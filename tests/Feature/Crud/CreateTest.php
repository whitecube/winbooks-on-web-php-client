<?php

use Whitecube\Winbooks\Winbooks;
use function Tests\test_folder;
use function Tests\cleanup;
use Whitecube\Winbooks\Models\Third;
use Whitecube\Winbooks\Models\Customer;

beforeEach(function() {
    $this->winbooks = new Winbooks();
});

it('can add a customer', function() {
    test_folder();

    $customer = [
        'MemoType' => '1',
        'Memo' => 'This is a memo for Alice Wilder',
        'Third' => [
            'Name' => 'Alice Wilder',
            'Website' => 'www.alice-wilder.com',
            'Code' => 'ALICE'
        ]
    ];

    $result = $this->winbooks->add('Customer', 'ALICE', $customer);

    $this->assertStringContainsString('Customer/ALICE/Folder/PARFIWEB_DEMO', $result['Href']);

    cleanup('Customer', 'ALICE');
});


it('can add a list of customers at once', function() {
    test_folder();

    $first = rand(999, 99999);
    $second = rand(999, 99999);
    $third = rand(999, 99999);

    $customers = [
        [
            'Code' => $first,
            'Third' => [
                'Code' => $first,
                'Name' => 'MR TEST ' . $first,
                'Website' => 'https://' . $first . '.com',
                'VatNumber' => '0000000196',
            ]
        ],
        [
            'Code' => $second,
            'Third' => [
                'Code' => $second,
                'Name' => 'MR TEST ' . $second,
                'Website' => 'https://' . $second . '.com'
            ]
        ],
        [
            'Code' => $third,
            'Third' => [
                'Code' => $third,
                'Name' => 'MR TEST ' . $third,
                'Website' => 'https://' . $third . '.com'
            ]
        ]
    ];

    $this->winbooks->addMany('Customers', $customers);
    $firstCustomer = $this->winbooks->get('Customer', $first);

    expect($firstCustomer->Code)->toBe((string) $first);

    cleanup('Customer', $first, $second, $third);
});


it('can add a customer from a customer model instance', function() {
    test_folder();

    $customer = new Customer([
        'Code' => 'ALICE',
        'Third' => new Third([
            'Code' => 'ALICE',
            'Name' => 'Alice Wilder',
            'Website' => 'www.alice-wilder.com'
        ])
    ]);

    $result = $this->winbooks->addModel($customer);

    $this->assertStringContainsString('Customer/ALICE/Folder/PARFIWEB_DEMO', $result['Href']);

    cleanup('Customer', 'ALICE');
});

it('can add a list of customer models', function() {
    test_folder();

    $customers = [
        new Customer([
            'Code' => 'ALICE',
            'Third' => new Third([
                'Code' => 'ALICE',
                'Name' => 'Alice Wilder',
                'Website' => 'www.alice-wilder.com'
            ])
        ]),
        new Customer([
            'Code' => 'ALICE2',
            'Third' => new Third([
                'Code' => 'ALICE2',
                'Name' => 'Alice2 Wilder',
                'Website' => 'www.alice2-wilder.com'
            ])
        ])
    ];

    $this->winbooks->addModels($customers);

    $alice = $this->winbooks->get('Customer', 'ALICE');

    expect($alice->Code)->toBe('ALICE');

    $alice2 = $this->winbooks->get('Customer', 'ALICE2');
    
    expect($alice2->Code)->toBe('ALICE2');

    cleanup('Customer', 'ALICE', 'ALICE2');
});
