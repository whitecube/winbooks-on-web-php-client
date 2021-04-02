<?php

use Whitecube\Winbooks\Winbooks;
use function Tests\test_folder;
use function Tests\authenticate;

beforeEach(function() {
    $this->winbooks = new Winbooks();
});


it('can get all customers from a specific folder', function() {
    authenticate();
    $data = $this->winbooks->folder('PARFIWEB_DEMO')->all('Customers');
    assertIsArray($data);
    assertObjectHasAttribute('Code', $data[0]);
});


it('can get a customer by code', function() {
    test_folder();
    $customer = $this->winbooks->get('Customer', 'ARTHUR');
    assertIsObject($customer);
    assertObjectHasAttribute('Code', $customer);
    assertEquals('ARTHUR', $customer->Code);
});


it('can get varying amounts of nested data', function() {
    test_folder();

    $first = $this->winbooks->get('Customer', 'ARTHUR');
    $second = $this->winbooks->get('Customer', 'ARTHUR', 2);
    $third = $this->winbooks->get('Customer', 'ARTHUR', 3);

    assertObjectNotHasAttribute('Third', $first);
    assertObjectHasAttribute('Third', $second);
    assertObjectHasAttribute('Civility', $third->Third);
});
