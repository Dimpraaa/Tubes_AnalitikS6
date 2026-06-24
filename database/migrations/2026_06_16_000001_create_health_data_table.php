<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabel untuk menyimpan data Early Wakeup Health Dataset (62 kolom + flags)
     */
    public function up(): void
    {
        Schema::create('health_data', function (Blueprint $table) {
            $table->id();

            // === Demografi ===
            $table->string('person_id', 20)->unique();
            $table->integer('age');
            $table->string('gender', 20);
            $table->decimal('height_cm', 6, 2);
            $table->decimal('weight_kg', 6, 2);
            $table->decimal('bmi', 6, 2);
            $table->string('country', 50);
            $table->string('occupation', 50);
            $table->string('marital_status', 30);

            // === Pola Tidur ===
            $table->string('wake_up_time', 10);
            $table->string('sleep_time', 10);
            $table->decimal('sleep_duration_hours', 5, 2);
            $table->decimal('sleep_quality_score', 5, 2);
            $table->integer('number_of_night_awakenings');
            $table->decimal('weekend_sleep_difference_hours', 5, 2);
            $table->integer('nap_frequency_per_week');
            $table->decimal('screen_time_before_bed_hours', 5, 2);

            // === Aktivitas Fisik ===
            $table->integer('exercise_frequency_per_week');
            $table->integer('exercise_duration_minutes');
            $table->string('exercise_type', 30);
            $table->integer('daily_steps');
            $table->string('morning_workout', 10);
            $table->string('workout_intensity', 20);
            $table->string('gym_member', 10);

            // === Nutrisi ===
            $table->integer('daily_calorie_intake');
            $table->decimal('water_intake_liters', 5, 2);
            $table->integer('fruit_intake_per_day');
            $table->integer('vegetable_intake_per_day');
            $table->decimal('protein_intake_grams', 6, 2);
            $table->integer('sugary_drinks_per_week');
            $table->integer('fast_food_meals_per_week');
            $table->decimal('breakfast_regularity_score', 5, 2);

            // === Gaya Hidup ===
            $table->string('smoking_status', 20);
            $table->string('alcohol_consumption', 20);
            $table->decimal('stress_level', 5, 2);
            $table->decimal('working_hours_per_day', 5, 2);
            $table->decimal('sitting_hours_per_day', 5, 2);
            $table->decimal('outdoor_time_hours', 5, 2);
            $table->decimal('social_interaction_score', 5, 2);
            $table->string('meditation_practice', 10);

            // === Indikator Kesehatan ===
            $table->integer('resting_heart_rate');
            $table->integer('systolic_bp');
            $table->integer('diastolic_bp');
            $table->decimal('cholesterol_level', 6, 2);
            $table->decimal('blood_sugar_level', 6, 2);

            // === Skor Kesehatan ===
            $table->decimal('energy_level_score', 5, 2);
            $table->decimal('fatigue_level_score', 5, 2);
            $table->decimal('immune_health_score', 5, 2);
            $table->decimal('mood_score', 5, 2);
            $table->decimal('anxiety_score', 5, 2);
            $table->decimal('depression_risk_score', 5, 2);
            $table->decimal('productivity_score', 5, 2);
            $table->decimal('focus_concentration_score', 5, 2);
            $table->decimal('life_satisfaction_score', 5, 2);

            // === Risiko Penyakit ===
            $table->string('obesity_risk', 20);
            $table->string('hypertension_risk', 20);
            $table->string('diabetes_risk', 20);
            $table->string('cardiovascular_risk', 20);
            $table->string('sleep_disorder_risk', 20);

            // === Summary ===
            $table->decimal('health_score', 6, 2);
            $table->string('fitness_level', 20);
            $table->decimal('healthy_aging_score', 6, 2);
            $table->string('wellness_category', 20);
            $table->string('early_waker', 10);

            // === Data Cleaning Flags ===
            $table->boolean('is_outlier')->default(false);

            $table->timestamps();
        });

        // Tabel untuk menyimpan cleaning report JSON
        Schema::create('cleaning_reports', function (Blueprint $table) {
            $table->id();
            $table->json('report_data');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('health_data');
        Schema::dropIfExists('cleaning_reports');
    }
};
