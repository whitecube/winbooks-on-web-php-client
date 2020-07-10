<?php

use Winbooks\Winbooks;
use function Tests\authenticate;

beforeEach(function() {
    $this->winbooks = new Winbooks();
});


it('can create a winbooks instance', function() {
    assertInstanceOf(Winbooks::class, $this->winbooks);
});


it('throws an exception if used without setting a folder', function() {
    authenticate();
    $this->winbooks->all('Customers');
})->throws(\Winbooks\Exceptions\UndefinedFolderException::class);


it('can get all customers from a specific folder', function() {
    authenticate();
    $data = $this->winbooks->folder('PARFIWEB_DEMO')->all('Customers');
    assertIsArray($data);
    assertObjectHasAttribute('Code', $data[0]);
});


it('can get a customer by code', function() {
    authenticate();
    $customer = $this->winbooks->folder('PARFIWEB_DEMO')->get('Customer', 'ARTHUR');
    assertIsObject($customer);
    assertObjectHasAttribute('Code', $customer);
    assertEquals('ARTHUR', $customer->Code);
});

