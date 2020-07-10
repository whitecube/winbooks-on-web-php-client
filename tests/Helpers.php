<?php

namespace Tests;

$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

function authenticate() {
    $email = $_ENV['WOW_TEST_EMAIL'];
    $token = $_ENV['WOW_TEST_EXCHANGE_TOKEN'];
    test()->winbooks->authenticate($email, $token);
}
