<?php

namespace Tests;

$_ENV['WOW_TEST_EMAIL'] = getenv('WOW_TEST_EMAIL');
$_ENV['WOW_TEST_EXCHANGE_TOKEN'] = getenv('WOW_TEST_EXCHANGE_TOKEN');

if(!$_ENV['WOW_TEST_EMAIL']) {
    $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
}

function authenticate() {
    $email = $_ENV['WOW_TEST_EMAIL'];
    $token = $_ENV['WOW_TEST_EXCHANGE_TOKEN'];
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
