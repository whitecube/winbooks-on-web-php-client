<?php

namespace Tests;

function credentials() {
    $email = getenv('WOW_TEST_EMAIL');
    $token = getenv('WOW_TEST_EXCHANGE_TOKEN');

    if(! $email || ! $token) {
        $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
        $dotenv->load();

        $email = $_ENV['WOW_TEST_EMAIL'];
        $token = $_ENV['WOW_TEST_EXCHANGE_TOKEN'];
    }

    return [$email, $token];
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
