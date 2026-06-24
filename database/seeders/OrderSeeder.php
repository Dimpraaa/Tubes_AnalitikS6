<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvFile = public_path('data/cleaned_ecommerce.csv');
        if (!file_exists($csvFile)) {
            $this->command->error("File CSV tidak ditemukan: " . $csvFile);
            return;
        }

        $this->command->info("Membaca file CSV...");
        
        $dataToInsert = [];
        $header = null;
        
        // Baca baris per baris
        if (($handle = fopen($csvFile, 'r')) !== false) {
            while (($row = fgetcsv($handle, 10000, ',')) !== false) {
                if (!$header) {
                    $header = $row;
                    // Format header to snake_case
                    $header = array_map(function($val) {
                        return strtolower(str_replace([' ', '/'], '_', $val));
                    }, $header);
                    continue;
                }

                $record = array_combine($header, $row);
                
                // Set default untuk field tertentu jika kosong
                $record['created_at'] = now();
                $record['updated_at'] = now();
                
                // Pastikan order_id tidak terlalu panjang / error
                if (!isset($record['order_id']) || empty($record['order_id'])) continue;

                $dataToInsert[] = $record;

                // Insert per 1000 baris agar memory tidak jebol dan performa cepat
                if (count($dataToInsert) >= 1000) {
                    DB::table('orders')->insert($dataToInsert);
                    $dataToInsert = [];
                }
            }
            fclose($handle);
            
            // Insert sisa data
            if (count($dataToInsert) > 0) {
                DB::table('orders')->insert($dataToInsert);
            }
        }
        
        $this->command->info("Data berhasil diimport ke tabel orders!");
    }
}
