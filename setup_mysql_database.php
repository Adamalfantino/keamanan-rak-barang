<?php
/**
 * Script untuk setup database MySQL Smart Rack Security System
 * Jalankan setelah membuat database MySQL dengan create_mysql_tables.sql
 */

require_once __DIR__ . '/vendor/autoload.php';

// Load Laravel app
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "🚀 Setting up Smart Rack Security MySQL Database...\n\n";

try {
    // Test database connection
    echo "📡 Testing database connection...\n";
    DB::connection()->getPdo();
    echo "✅ Database connection successful!\n\n";
    
    // Check if tables exist
    echo "📊 Checking database tables...\n";
    $tables = [
        'users', 'devices', 'sensors', 'pir_readings', 
        'door_readings', 'vibration_readings', 'door_access_readings',
        'lora_messages', 'sensor_readings', 'alerts', 
        'activity_logs', 'system_settings'
    ];
    
    $existingTables = [];
    foreach ($tables as $table) {
        if (Schema::hasTable($table)) {
            $existingTables[] = $table;
            echo "✅ Table '{$table}' exists\n";
        } else {
            echo "❌ Table '{$table}' missing\n";
        }
    }
    
    if (count($existingTables) === count($tables)) {
        echo "\n🎉 All tables exist! Database setup is complete.\n\n";
        
        // Show database statistics
        echo "📈 Database Statistics:\n";
        foreach ($tables as $table) {
            try {
                $count = DB::table($table)->count();
                echo "   {$table}: {$count} records\n";
            } catch (Exception $e) {
                echo "   {$table}: Error reading - {$e->getMessage()}\n";
            }
        }
        
        echo "\n📱 Dashboard Summary:\n";
        try {
            $summary = DB::select('SELECT * FROM dashboard_summary')[0];
            echo "   Online Devices: {$summary->online_devices}\n";
            echo "   Offline Devices: {$summary->offline_devices}\n";
            echo "   Unacknowledged Alerts (24h): {$summary->unacknowledged_alerts_24h}\n";
            echo "   Motions (24h): {$summary->motions_24h}\n";
            echo "   Door Accesses (24h): {$summary->door_accesses_24h}\n";
            echo "   Abnormal Vibrations (24h): {$summary->abnormal_vibrations_24h}\n";
        } catch (Exception $e) {
            echo "   Dashboard view not available: {$e->getMessage()}\n";
        }
        
    } else {
        echo "\n⚠️  Some tables are missing. Please run the create_mysql_tables.sql script first:\n";
        echo "   mysql -u root -p < create_mysql_tables.sql\n";
    }
    
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    echo "\n🔧 Please check your .env configuration:\n";
    echo "   DB_CONNECTION=mysql\n";
    echo "   DB_HOST=127.0.0.1\n";
    echo "   DB_PORT=3306\n";
    echo "   DB_DATABASE=smart_rack_security\n";
    echo "   DB_USERNAME=root\n";
    echo "   DB_PASSWORD=your_password\n\n";
    echo "📝 And make sure MySQL server is running and database exists.\n";
}

echo "\n🔗 Next Steps:\n";
echo "1. Start your Arduino with the updated code (arduino_fixed_code.ino)\n";
echo "2. Test LoRa communication\n";
echo "3. Monitor data in the database\n";
echo "4. Access Laravel application: php artisan serve\n";
?>