<?php

namespace Tests;


function credentials() {
    if(! isset($_ENV['WOW_TEST_EMAIL']) || ! isset($_ENV['WOW_TEST_EXCHANGE_TOKEN'])) {
        $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
        $dotenv->load();
    }

    return [
        $_ENV['WOW_TEST_EMAIL'],
        $_ENV['WOW_TEST_EXCHANGE_TOKEN']
    ];
}

function authenticate() {
    [$email, $token] = credentials();

    test()->winbooks->authenticate($email, $token);
}

function test_folder() {
    authenticate();
    test()->winbooks->folder('PARFIWEB_DEMO');
}

function cleanup($om, ...$codes) {
    foreach($codes as $code) {
        test()->winbooks->delete($om, $code);
    }
}
