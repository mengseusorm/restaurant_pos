<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            // Get the current columns in the table
            DB::statement('PRAGMA foreign_keys=OFF');
            DB::statement('CREATE TABLE session_items_backup AS SELECT * FROM session_items');
            
            // Drop the old table
            DB::statement('DROP TABLE session_items');
            
            // Create new table with duration nullable
            DB::statement("CREATE TABLE session_items (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                sub_session_id INTEGER NOT NULL,
                item_id INTEGER NOT NULL,
                room_id INTEGER,
                therapist_id INTEGER,
                start_time DATETIME,
                end_time DATETIME,
                started_time DATETIME,
                ended_time DATETIME,
                duration INTEGER,
                price NUMERIC NOT NULL DEFAULT 0,
                discount NUMERIC NOT NULL DEFAULT 0,
                final_price NUMERIC NOT NULL DEFAULT 0,
                status TEXT NOT NULL DEFAULT 'pending',
                notes TEXT,
                created_at DATETIME,
                updated_at DATETIME,
                FOREIGN KEY (sub_session_id) REFERENCES sub_sessions(id) ON DELETE CASCADE,
                FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE CASCADE,
                FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE SET NULL,
                FOREIGN KEY (therapist_id) REFERENCES users(id) ON DELETE SET NULL
            )");
            
            // Insert data, handling columns that might not exist in the backup
            DB::statement("INSERT INTO session_items 
                SELECT 
                    id, 
                    sub_session_id, 
                    item_id, 
                    room_id, 
                    therapist_id, 
                    start_time, 
                    end_time, 
                    started_time, 
                    ended_time, 
                    duration, 
                    price, 
                    discount, 
                    final_price, 
                    status, 
                    notes, 
                    created_at, 
                    updated_at 
                FROM session_items_backup");
            
            DB::statement('DROP TABLE session_items_backup');
            DB::statement('PRAGMA foreign_keys=ON');
        } else {
            DB::statement('ALTER TABLE session_items MODIFY COLUMN duration INT UNSIGNED NULL DEFAULT NULL');
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys=OFF');
            DB::statement('CREATE TABLE session_items_backup AS SELECT * FROM session_items');
            
            DB::statement('DROP TABLE session_items');
            
            DB::statement("CREATE TABLE session_items (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                sub_session_id INTEGER NOT NULL,
                item_id INTEGER NOT NULL,
                room_id INTEGER,
                therapist_id INTEGER,
                start_time DATETIME,
                end_time DATETIME,
                started_time DATETIME,
                ended_time DATETIME,
                duration INTEGER NOT NULL DEFAULT 0,
                price NUMERIC NOT NULL DEFAULT 0,
                discount NUMERIC NOT NULL DEFAULT 0,
                final_price NUMERIC NOT NULL DEFAULT 0,
                status TEXT NOT NULL DEFAULT 'pending',
                notes TEXT,
                created_at DATETIME,
                updated_at DATETIME,
                FOREIGN KEY (sub_session_id) REFERENCES sub_sessions(id) ON DELETE CASCADE,
                FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE CASCADE,
                FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE SET NULL,
                FOREIGN KEY (therapist_id) REFERENCES users(id) ON DELETE SET NULL
            )");
            
            DB::statement("INSERT INTO session_items 
                SELECT 
                    id, 
                    sub_session_id, 
                    item_id, 
                    room_id, 
                    therapist_id, 
                    start_time, 
                    end_time, 
                    started_time, 
                    ended_time, 
                    duration, 
                    price, 
                    discount, 
                    final_price, 
                    status, 
                    notes, 
                    created_at, 
                    updated_at 
                FROM session_items_backup");
            
            DB::statement('DROP TABLE session_items_backup');
            DB::statement('PRAGMA foreign_keys=ON');
        } else {
            DB::statement('ALTER TABLE session_items MODIFY COLUMN duration INT UNSIGNED NOT NULL DEFAULT 0');
        }
    }
};
