<?php
/**
 * Family Members Database Migration
 * Run this file once to create the necessary tables for family member management
 */

require_once __DIR__ . '/../config/database.php';

echo "Starting Family Members Database Migration...\n\n";

try {
    // Read the SQL file
    $sql = file_get_contents(__DIR__ . '/family_members_schema.sql');
    
    // Remove delimiter commands as they don't work well with PDO
    $sql = preg_replace('/DELIMITER\s+\$\$/i', '', $sql);
    $sql = preg_replace('/DELIMITER\s+;/i', '', $sql);
    $sql = str_replace('$$', ';', $sql);
    
    // Split into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    $successCount = 0;
    $errorCount = 0;
    
    foreach ($statements as $statement) {
        if (empty($statement)) continue;
        
        try {
            $pdo->exec($statement);
            $successCount++;
            
            // Identify what was created
            if (stripos($statement, 'CREATE TABLE') !== false) {
                if (stripos($statement, 'family_members') !== false && stripos($statement, 'family_member_logs') === false) {
                    echo "✓ Created table: family_members\n";
                } elseif (stripos($statement, 'family_member_logs') !== false) {
                    echo "✓ Created table: family_member_logs\n";
                }
            } elseif (stripos($statement, 'CREATE TRIGGER') !== false) {
                echo "✓ Created trigger: after_family_member_log_insert\n";
            }
            
        } catch (PDOException $e) {
            // Check if error is because table already exists
            if (strpos($e->getMessage(), 'already exists') !== false) {
                echo "⚠ Table already exists, skipping...\n";
            } else {
                $errorCount++;
                echo "✗ Error: " . $e->getMessage() . "\n";
            }
        }
    }
    
    echo "\n";
    echo "==========================================\n";
    echo "Migration completed!\n";
    echo "Successful operations: $successCount\n";
    if ($errorCount > 0) {
        echo "Errors encountered: $errorCount\n";
    }
    echo "==========================================\n\n";
    
    // Verify tables exist
    echo "Verifying tables...\n";
    
    $tableCheck = $pdo->query("SHOW TABLES LIKE 'family_members'")->fetchColumn();
    if ($tableCheck) {
        echo "✓ family_members table exists\n";
        
        // Count columns
        $columns = $pdo->query("SHOW COLUMNS FROM family_members")->fetchAll();
        echo "  - Contains " . count($columns) . " columns\n";
    } else {
        echo "✗ family_members table NOT found!\n";
    }
    
    $logTableCheck = $pdo->query("SHOW TABLES LIKE 'family_member_logs'")->fetchColumn();
    if ($logTableCheck) {
        echo "✓ family_member_logs table exists\n";
        
        $columns = $pdo->query("SHOW COLUMNS FROM family_member_logs")->fetchAll();
        echo "  - Contains " . count($columns) . " columns\n";
    } else {
        echo "✗ family_member_logs table NOT found!\n";
    }
    
    // Check trigger
    $triggers = $pdo->query("SHOW TRIGGERS LIKE 'family_member_logs'")->fetchAll();
    if (count($triggers) > 0) {
        echo "✓ Trigger 'after_family_member_log_insert' exists\n";
    } else {
        echo "⚠ Trigger not found (may already exist or need manual creation)\n";
    }
    
    echo "\nYou can now use the Family Member Management feature!\n";
    echo "Navigate to: Admin Panel → Homeowners → Click the People icon\n";
    
} catch (Exception $e) {
    echo "Fatal error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
