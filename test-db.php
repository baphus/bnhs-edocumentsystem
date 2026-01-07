<?php

$host = 'serverless-eu-west-2.sysp0000.db1.skysql.com';
$port = '4003';
$db = 'bnhs_edocument';
$user = 'dbpwf42517559';
$pass = '?8B]dp9BnlxcTTP7XZn8x';

echo "Testing database connection...\n";
echo "Host: $host:$port\n";
echo "Database: $db\n";
echo "Username: $user\n";
echo "Password length: " . strlen($pass) . " characters\n\n";

// Test 1: Without SSL
echo "Test 1: Without SSL options\n";
try {
    $dsn = "mysql:host=$host;port=$port;dbname=$db";
    $pdo = new PDO($dsn, $user, $pass);
    echo "✓ Connected successfully!\n\n";
    $pdo = null;
} catch (PDOException $e) {
    echo "✗ Failed: " . $e->getMessage() . "\n\n";
}

// Test 2: With SSL verify disabled
echo "Test 2: With SSL verify disabled\n";
try {
    $dsn = "mysql:host=$host;port=$port;dbname=$db";
    $options = [
        PHP_VERSION_ID >= 80500 ? Pdo\Mysql::ATTR_SSL_VERIFY_SERVER_CERT : PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
    ];
    $pdo = new PDO($dsn, $user, $pass, $options);
    echo "✓ Connected successfully!\n";
    $pdo = null;
} catch (PDOException $e) {
    echo "✗ Failed: " . $e->getMessage() . "\n\n";
}
