<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HealthDataSeeder extends Seeder
{
    /**
     * Import cleaned CSV (output dari Python) ke database.
     * Juga import cleaning_report.json ke tabel cleaning_reports.
     */
    public function run(): void
    {
        $this->command->info('Starting HealthData import...');

        // Path ke output Python
        $csvPath = base_path('python/output/cleaned_health_data.csv');
        $reportPath = base_path('python/output/cleaning_report.json');

        if (!file_exists($csvPath)) {
            $this->command->error("Cleaned CSV not found: {$csvPath}");
            $this->command->error("Please run: python python/data_cleaning.py first!");
            return;
        }

        // Clear existing data
        DB::table('health_data')->truncate();
        DB::table('cleaning_reports')->truncate();

        // Import CSV
        $handle = fopen($csvPath, 'r');
        $header = fgetcsv($handle);

        // Map CSV columns to DB columns (snake_case)
        $columnMap = [];
        foreach ($header as $col) {
            $dbCol = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $col));
            // Fix specific mappings
            $dbCol = str_replace(
                ['b_m_i', 's_ystolic_b_p', 'd_iastolic_b_p', 'i_d'],
                ['bmi', 'systolic_bp', 'diastolic_bp', 'id'],
                $dbCol
            );
            // Direct lowercase mapping for already-snake_case columns
            $dbCol = strtolower($col);
            $columnMap[] = $dbCol;
        }

        $batch = [];
        $batchSize = 500;
        $totalImported = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $record = [];
            foreach ($row as $i => $value) {
                $colName = $columnMap[$i] ?? null;
                if ($colName === null) continue;

                // Handle empty values
                if ($value === '' || $value === 'nan' || $value === 'NaN') {
                    $record[$colName] = null;
                } else {
                    $record[$colName] = $value;
                }
            }

            // Convert is_outlier from Python True/False to 1/0
            if (isset($record['is_outlier'])) {
                $record['is_outlier'] = in_array(strtolower($record['is_outlier']), ['true', '1', 'yes']) ? 1 : 0;
            }

            $record['created_at'] = now();
            $record['updated_at'] = now();

            $batch[] = $record;

            if (count($batch) >= $batchSize) {
                DB::table('health_data')->insert($batch);
                $totalImported += count($batch);
                $batch = [];
                $this->command->info("  Imported {$totalImported} rows...");
            }
        }

        // Insert remaining
        if (!empty($batch)) {
            DB::table('health_data')->insert($batch);
            $totalImported += count($batch);
        }

        fclose($handle);
        $this->command->info("✓ Total imported: {$totalImported} rows");

        // Import cleaning report
        if (file_exists($reportPath)) {
            $reportJson = file_get_contents($reportPath);
            DB::table('cleaning_reports')->insert([
                'report_data' => $reportJson,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->command->info("✓ Cleaning report imported");
        }

        $this->command->info('HealthData import completed!');
    }
}
