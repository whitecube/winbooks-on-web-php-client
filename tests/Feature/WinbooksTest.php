<?php

use Whitecube\Winbooks\Winbooks;
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
})->throws(\Whitecube\Winbooks\Exceptions\UndefinedFolderException::class);
