<?php

use Whitecube\Winbooks\Winbooks;
use function Tests\authenticate;

beforeEach(function() {
    $this->winbooks = new Winbooks();
});

it('throws an exception if used without authenticating first', function() {
    $this->winbooks->folder('PARFIWEB_DEMO')->all('Customers');
})->throws(\Whitecube\Winbooks\Exceptions\UnauthenticatedException::class);


it('can authenticate with an e-mail address and an exchange token', function() {
    authenticate();
    expect($this->winbooks->authenticated())->toBeTrue();
});


it('can authenticate by passing the access and refresh tokens to the constructor', function() {
    $winbooks = new Winbooks('foo-access-token', 'bar-refresh-token');
    expect($winbooks->authenticated())->toBeTrue();
});


it('uses the refresh token to get a new access token', function() {
    authenticate();

    // invalidate access_token
    $this->winbooks->setAccessToken('abc');
    $this->winbooks->initialize();

    // it should still work, since it will just get a new access token with the refresh token
    $customer = $this->winbooks->folder('PARFIWEB_DEMO')->get('Customer', 'ARTHUR');
    
    expect($customer->Code)->toBe('ARTHUR');
});


it('throws exception if all tokens are invalid', function() {
    authenticate();

    $this->winbooks->setAccessToken('abc');
    $this->winbooks->setRefreshToken('def');
    $this->winbooks->initialize();

    $this->winbooks->folder('PARFIWEB_DEMO')->get('Customer', 'ARTHUR');
})->throws(\Whitecube\Winbooks\Exceptions\InvalidRefreshTokenException::class);


it('still throws underlying API exceptions', function() {
    authenticate();

    $this->winbooks->folder('FOO_FOLDER')->all('Customers');
})->throws(\GuzzleHttp\Exception\ClientException::class);
