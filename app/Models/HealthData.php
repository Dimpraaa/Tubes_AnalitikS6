<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HealthData extends Model
{
    protected $table = 'health_data';

    protected $fillable = [
        // Demografi
        'person_id', 'age', 'gender', 'height_cm', 'weight_kg', 'bmi',
        'country', 'occupation', 'marital_status',

        // Pola Tidur
        'wake_up_time', 'sleep_time', 'sleep_duration_hours', 'sleep_quality_score',
        'number_of_night_awakenings', 'weekend_sleep_difference_hours',
        'nap_frequency_per_week', 'screen_time_before_bed_hours',

        // Aktivitas Fisik
        'exercise_frequency_per_week', 'exercise_duration_minutes', 'exercise_type',
        'daily_steps', 'morning_workout', 'workout_intensity', 'gym_member',

        // Nutrisi
        'daily_calorie_intake', 'water_intake_liters', 'fruit_intake_per_day',
        'vegetable_intake_per_day', 'protein_intake_grams', 'sugary_drinks_per_week',
        'fast_food_meals_per_week', 'breakfast_regularity_score',

        // Gaya Hidup
        'smoking_status', 'alcohol_consumption', 'stress_level',
        'working_hours_per_day', 'sitting_hours_per_day', 'outdoor_time_hours',
        'social_interaction_score', 'meditation_practice',

        // Indikator Kesehatan
        'resting_heart_rate', 'systolic_bp', 'diastolic_bp',
        'cholesterol_level', 'blood_sugar_level',

        // Skor Kesehatan
        'energy_level_score', 'fatigue_level_score', 'immune_health_score',
        'mood_score', 'anxiety_score', 'depression_risk_score',
        'productivity_score', 'focus_concentration_score', 'life_satisfaction_score',

        // Risiko Penyakit
        'obesity_risk', 'hypertension_risk', 'diabetes_risk',
        'cardiovascular_risk', 'sleep_disorder_risk',

        // Summary
        'health_score', 'fitness_level', 'healthy_aging_score',
        'wellness_category', 'early_waker',

        // Flags
        'is_outlier',
    ];

    protected $casts = [
        'age' => 'integer',
        'height_cm' => 'float',
        'weight_kg' => 'float',
        'bmi' => 'float',
        'sleep_duration_hours' => 'float',
        'sleep_quality_score' => 'float',
        'number_of_night_awakenings' => 'integer',
        'weekend_sleep_difference_hours' => 'float',
        'nap_frequency_per_week' => 'integer',
        'screen_time_before_bed_hours' => 'float',
        'exercise_frequency_per_week' => 'integer',
        'exercise_duration_minutes' => 'integer',
        'daily_steps' => 'integer',
        'daily_calorie_intake' => 'integer',
        'water_intake_liters' => 'float',
        'fruit_intake_per_day' => 'integer',
        'vegetable_intake_per_day' => 'integer',
        'protein_intake_grams' => 'float',
        'sugary_drinks_per_week' => 'integer',
        'fast_food_meals_per_week' => 'integer',
        'breakfast_regularity_score' => 'float',
        'stress_level' => 'float',
        'working_hours_per_day' => 'float',
        'sitting_hours_per_day' => 'float',
        'outdoor_time_hours' => 'float',
        'social_interaction_score' => 'float',
        'resting_heart_rate' => 'integer',
        'systolic_bp' => 'integer',
        'diastolic_bp' => 'integer',
        'cholesterol_level' => 'float',
        'blood_sugar_level' => 'float',
        'energy_level_score' => 'float',
        'fatigue_level_score' => 'float',
        'immune_health_score' => 'float',
        'mood_score' => 'float',
        'anxiety_score' => 'float',
        'depression_risk_score' => 'float',
        'productivity_score' => 'float',
        'focus_concentration_score' => 'float',
        'life_satisfaction_score' => 'float',
        'health_score' => 'float',
        'healthy_aging_score' => 'float',
        'is_outlier' => 'boolean',
    ];

    // ========================
    // SCOPES
    // ========================

    /**
     * Scope: hanya early wakers
     */
    public function scopeEarlyWakers($query)
    {
        return $query->where('early_waker', 'Yes');
    }

    /**
     * Scope: hanya non-early wakers
     */
    public function scopeNonEarlyWakers($query)
    {
        return $query->where('early_waker', 'No');
    }

    /**
     * Scope: filter by country
     */
    public function scopeByCountry($query, $country)
    {
        return $query->where('country', $country);
    }

    /**
     * Scope: filter by gender
     */
    public function scopeByGender($query, $gender)
    {
        return $query->where('gender', $gender);
    }

    /**
     * Scope: filter by wellness category
     */
    public function scopeByWellnessCategory($query, $category)
    {
        return $query->where('wellness_category', $category);
    }

    /**
     * Scope: filter by age group
     */
    public function scopeByAgeGroup($query, $group)
    {
        return match ($group) {
            '18-30' => $query->whereBetween('age', [18, 30]),
            '31-45' => $query->whereBetween('age', [31, 45]),
            '46-60' => $query->whereBetween('age', [46, 60]),
            '61+' => $query->where('age', '>', 60),
            default => $query,
        };
    }

    /**
     * Scope: exclude outliers
     */
    public function scopeClean($query)
    {
        return $query->where('is_outlier', false);
    }
}
