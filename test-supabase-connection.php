<?php

/**
 * Supabase PostgreSQL Connection Test Script
 * 
 * Fill in your Supabase credentials below and run:
 * php test-supabase-connection.php
 */

// ============================================
// YOUR SUPABASE CREDENTIALS HERE
// ============================================
$host     = 'aws-1-ap-northeast-2.pooler.supabase.com';  // Replace with your project host
$port     = '5432';
$database = 'postgres';
$username = 'postgres.euyxhgwzncwfoxinmvtc';
$password = 'YZOUpsCriwsmrAb5';              // Replace with your Supabase password
$sslmode  = 'require';

// ============================================
// TEST SCRIPT (Don't modify below)
// ============================================

echo "╔══════════════════════════════════════════════════════════╗\n";
echo "║     Supabase PostgreSQL Connection Test                 ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n\n";

echo "Testing connection to: {$host}\n";
echo "Database: {$database}\n";
echo "Username: {$username}\n";
echo "SSL Mode: {$sslmode}\n\n";

try {
    // Build the DSN (Data Source Name)
    $dsn = "pgsql:host={$host};port={$port};dbname={$database};sslmode={$sslmode}";
    
    echo "[1/4] Creating PDO connection...\n";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 5,
    ]);
    
    echo "✓ Connection successful!\n\n";
    
    // Test 1: Check PostgreSQL version
    echo "[2/4] Checking PostgreSQL version...\n";
    $stmt = $pdo->query('SELECT version()');
    $version = $stmt->fetch();
    echo "✓ PostgreSQL Version: " . substr($version['version'], 0, 80) . "...\n\n";
    
    // Test 2: Check current database and user
    echo "[3/4] Checking database info...\n";
    $stmt = $pdo->query('SELECT current_database(), current_user, current_schema()');
    $info = $stmt->fetch();
    echo "✓ Current Database: {$info['current_database']}\n";
    echo "✓ Current User: {$info['current_user']}\n";
    echo "✓ Current Schema: {$info['current_schema']}\n\n";
    
    // Test 3: List existing tables
    echo "[4/4] Checking existing tables...\n";
    $stmt = $pdo->query("
        SELECT table_name 
        FROM information_schema.tables 
        WHERE table_schema = 'public' 
        AND table_type = 'BASE TABLE'
        ORDER BY table_name
    ");
    $tables = $stmt->fetchAll();
    
    if (count($tables) > 0) {
        echo "✓ Found " . count($tables) . " table(s) in public schema:\n";
        foreach ($tables as $table) {
            echo "  - {$table['table_name']}\n";
        }
    } else {
        echo "✓ No tables found (fresh database - ready for migrations)\n";
    }
    
    echo "\n";
    echo "╔══════════════════════════════════════════════════════════╗\n";
    echo "║  ✓ ALL TESTS PASSED - Connection is working!            ║\n";
    echo "╚══════════════════════════════════════════════════════════╝\n";
    echo "\nYou can now update your .env file with these credentials.\n";
    
} catch (PDOException $e) {
    echo "\n";
    echo "╔══════════════════════════════════════════════════════════╗\n";
    echo "║  ✗ CONNECTION FAILED                                     ║\n";
    echo "╚══════════════════════════════════════════════════════════╝\n\n";
    
    echo "Error Details:\n";
    echo "Code: " . $e->getCode() . "\n";
    echo "Message: " . $e->getMessage() . "\n\n";
    
    echo "Common issues:\n";
    echo "1. Check your credentials are correct\n";
    echo "2. Verify your Supabase project host (db.xxxx.supabase.co)\n";
    echo "3. Ensure your IP is allowed (Supabase → Settings → Database)\n";
    echo "4. Check if PostgreSQL PDO extension is installed\n";
    echo "   Run: php -m | grep pdo_pgsql\n\n";
    
    exit(1);
}
