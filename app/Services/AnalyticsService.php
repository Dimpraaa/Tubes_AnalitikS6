<?php

namespace App\Services;

use App\Models\HealthData;
use Illuminate\Support\Facades\DB;

/**
 * AnalyticsService — EDA & Analisis Data
 * 
 * Menyediakan:
 * 1. Statistik Deskriptif
 * 2. Distribusi Data
 * 3. Pearson Correlation
 * 4. Perbandingan Early Waker vs Non-Early Waker
 * 5. Analisis Risiko
 */
class AnalyticsService
{
    /**
     * Get summary cards data (for dashboard top section)
     */
    public function getSummaryCards(array $filters = []): array
    {
        $query = $this->applyFilters(HealthData::query(), $filters);

        return [
            'total_respondents' => $query->count(),
            'avg_health_score' => round($query->avg('health_score'), 1),
            'avg_sleep_duration' => round($query->avg('sleep_duration_hours'), 1),
            'early_waker_pct' => $this->getEarlyWakerPercentage($filters),
            'avg_bmi' => round($query->avg('bmi'), 1),
            'avg_stress_level' => round($query->avg('stress_level'), 1),
        ];
    }

    /**
     * Get early waker percentage
     */
    private function getEarlyWakerPercentage(array $filters = []): float
    {
        $query = $this->applyFilters(HealthData::query(), $filters);
        $total = $query->count();
        if ($total === 0) return 0;

        $earlyWakers = $this->applyFilters(HealthData::query(), $filters)
            ->where('early_waker', 'Yes')
            ->count();

        return round(($earlyWakers / $total) * 100, 1);
    }

    /**
     * Chart 1: Bar Chart — Avg Health Score per Wellness Category (Early vs Non)
     */
    public function getHealthScoreByWellness(array $filters = []): array
    {
        $query = $this->applyFilters(HealthData::query(), $filters);

        $categories = ['Excellent', 'Good', 'Average', 'Poor'];
        $earlyData = [];
        $nonEarlyData = [];

        foreach ($categories as $cat) {
            $earlyData[] = round(
                (clone $query)->where('wellness_category', $cat)
                    ->where('early_waker', 'Yes')
                    ->avg('health_score') ?? 0, 1
            );
            $nonEarlyData[] = round(
                (clone $query)->where('wellness_category', $cat)
                    ->where('early_waker', 'No')
                    ->avg('health_score') ?? 0, 1
            );
        }

        return [
            'labels' => $categories,
            'datasets' => [
                ['label' => 'Early Waker', 'data' => $earlyData],
                ['label' => 'Non-Early Waker', 'data' => $nonEarlyData],
            ],
        ];
    }

    /**
     * Chart 2: Line Chart — Health Score Trend per Age Group
     */
    public function getHealthScoreByAgeGroup(array $filters = []): array
    {
        $query = $this->applyFilters(HealthData::query(), $filters);

        $ageGroups = [
            '18-30' => [18, 30],
            '31-45' => [31, 45],
            '46-60' => [46, 60],
            '61+' => [61, 120],
        ];

        $earlyData = [];
        $nonEarlyData = [];

        foreach ($ageGroups as $label => [$min, $max]) {
            $earlyData[] = round(
                (clone $query)->whereBetween('age', [$min, $max])
                    ->where('early_waker', 'Yes')
                    ->avg('health_score') ?? 0, 1
            );
            $nonEarlyData[] = round(
                (clone $query)->whereBetween('age', [$min, $max])
                    ->where('early_waker', 'No')
                    ->avg('health_score') ?? 0, 1
            );
        }

        return [
            'labels' => array_keys($ageGroups),
            'datasets' => [
                ['label' => 'Early Waker', 'data' => $earlyData],
                ['label' => 'Non-Early Waker', 'data' => $nonEarlyData],
            ],
        ];
    }

    /**
     * Chart 3: Donut Chart — Wellness Category Distribution
     */
    public function getWellnessDistribution(array $filters = []): array
    {
        $query = $this->applyFilters(HealthData::query(), $filters);

        $distribution = $query->select('wellness_category', DB::raw('COUNT(*) as count'))
            ->groupBy('wellness_category')
            ->pluck('count', 'wellness_category')
            ->toArray();

        $order = ['Excellent', 'Good', 'Fair', 'Poor'];
        $labels = [];
        $data = [];

        foreach ($order as $cat) {
            if (isset($distribution[$cat])) {
                $labels[] = $cat;
                $data[] = $distribution[$cat];
            }
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    /**
     * Chart 4: Scatter Plot — Sleep Duration vs Health Score
     */
    public function getSleepVsHealth(array $filters = []): array
    {
        $query = $this->applyFilters(HealthData::query(), $filters);

        // Sample 500 points for performance
        $earlyPoints = (clone $query)->where('early_waker', 'Yes')
            ->inRandomOrder()
            ->limit(250)
            ->select('sleep_duration_hours', 'health_score')
            ->get()
            ->map(fn($r) => ['x' => $r->sleep_duration_hours, 'y' => $r->health_score])
            ->toArray();

        $nonEarlyPoints = (clone $query)->where('early_waker', 'No')
            ->inRandomOrder()
            ->limit(250)
            ->select('sleep_duration_hours', 'health_score')
            ->get()
            ->map(fn($r) => ['x' => $r->sleep_duration_hours, 'y' => $r->health_score])
            ->toArray();

        return [
            'datasets' => [
                ['label' => 'Early Waker', 'data' => $earlyPoints],
                ['label' => 'Non-Early Waker', 'data' => $nonEarlyPoints],
            ],
        ];
    }

    /**
     * Chart 5: Stacked Bar — Risk Distribution by Early Waker Status
     */
    public function getRiskDistribution(array $filters = []): array
    {
        $query = $this->applyFilters(HealthData::query(), $filters);

        $riskCols = [
            'obesity_risk' => 'Obesity',
            'hypertension_risk' => 'Hypertension',
            'diabetes_risk' => 'Diabetes',
            'cardiovascular_risk' => 'Cardiovascular',
            'sleep_disorder_risk' => 'Sleep Disorder',
        ];

        $levels = ['Low', 'Medium', 'High'];
        $datasets = [];

        foreach ($levels as $level) {
            $earlyData = [];
            $nonEarlyData = [];

            foreach ($riskCols as $col => $label) {
                $earlyData[] = (clone $query)
                    ->where('early_waker', 'Yes')
                    ->where($col, $level)
                    ->count();
                $nonEarlyData[] = (clone $query)
                    ->where('early_waker', 'No')
                    ->where($col, $level)
                    ->count();
            }

            $datasets[] = [
                'label' => "{$level} Risk (Early)",
                'data' => $earlyData,
                'stack' => 'early',
            ];
            $datasets[] = [
                'label' => "{$level} Risk (Non-Early)",
                'data' => $nonEarlyData,
                'stack' => 'non-early',
            ];
        }

        return [
            'labels' => array_values($riskCols),
            'datasets' => $datasets,
        ];
    }

    /**
     * Chart 6: Correlation Matrix — Pearson Correlation
     */
    public function getCorrelationMatrix(array $filters = []): array
    {
        $query = $this->applyFilters(HealthData::query(), $filters);

        $columns = [
            'sleep_duration_hours',
            'health_score',
            'bmi',
            'stress_level',
            'exercise_frequency_per_week',
            'daily_steps',
            'resting_heart_rate',
            'mood_score',
        ];

        $labels = [
            'Sleep Duration',
            'Health Score',
            'BMI',
            'Stress Level',
            'Exercise Freq',
            'Daily Steps',
            'Heart Rate',
            'Mood Score',
        ];

        // Get data from DB
        $data = $query->select($columns)->limit(5000)->get()->toArray();

        if (empty($data)) {
            return ['labels' => $labels, 'matrix' => []];
        }

        // Calculate Pearson Correlation
        $matrix = [];
        $n = count($data);

        for ($i = 0; $i < count($columns); $i++) {
            $row = [];
            for ($j = 0; $j < count($columns); $j++) {
                if ($i === $j) {
                    $row[] = 1.0;
                    continue;
                }

                $colA = $columns[$i];
                $colB = $columns[$j];

                $valuesA = array_column($data, $colA);
                $valuesB = array_column($data, $colB);

                $row[] = $this->pearsonCorrelation($valuesA, $valuesB);
            }
            $matrix[] = $row;
        }

        return [
            'labels' => $labels,
            'matrix' => $matrix,
        ];
    }

    /**
     * Calculate Pearson Correlation Coefficient
     * 
     * r = Σ((xi - x̄)(yi - ȳ)) / √(Σ(xi - x̄)² × Σ(yi - ȳ)²)
     */
    private function pearsonCorrelation(array $x, array $y): float
    {
        $n = count($x);
        if ($n === 0) return 0;

        $meanX = array_sum($x) / $n;
        $meanY = array_sum($y) / $n;

        $sumXY = 0;
        $sumX2 = 0;
        $sumY2 = 0;

        for ($i = 0; $i < $n; $i++) {
            $dx = $x[$i] - $meanX;
            $dy = $y[$i] - $meanY;
            $sumXY += $dx * $dy;
            $sumX2 += $dx * $dx;
            $sumY2 += $dy * $dy;
        }

        $denominator = sqrt($sumX2 * $sumY2);
        if ($denominator == 0) return 0;

        return round($sumXY / $denominator, 3);
    }

    /**
     * Get key insights — dynamic storytelling data
     * Compares early waker vs non-early waker across multiple metrics
     */
    public function getKeyInsights(array $filters = []): array
    {
        $query = $this->applyFilters(HealthData::query(), $filters);

        $earlyAvgHealth = (clone $query)->where('early_waker', 'Yes')->avg('health_score') ?? 0;
        $nonEarlyAvgHealth = (clone $query)->where('early_waker', 'No')->avg('health_score') ?? 0;
        $healthDiff = round($earlyAvgHealth - $nonEarlyAvgHealth, 1);

        $earlyAvgStress = (clone $query)->where('early_waker', 'Yes')->avg('stress_level') ?? 0;
        $nonEarlyAvgStress = (clone $query)->where('early_waker', 'No')->avg('stress_level') ?? 0;
        $stressDiff = round($nonEarlyAvgStress - $earlyAvgStress, 1);

        $earlyTotal = (clone $query)->where('early_waker', 'Yes')->count();
        $earlyExcellent = (clone $query)->where('early_waker', 'Yes')->where('wellness_category', 'Excellent')->count();
        $nonEarlyTotal = (clone $query)->where('early_waker', 'No')->count();
        $nonEarlyExcellent = (clone $query)->where('early_waker', 'No')->where('wellness_category', 'Excellent')->count();

        $earlyExcPct = $earlyTotal > 0 ? round(($earlyExcellent / $earlyTotal) * 100, 1) : 0;
        $nonEarlyExcPct = $nonEarlyTotal > 0 ? round(($nonEarlyExcellent / $nonEarlyTotal) * 100, 1) : 0;

        return [
            [
                'title' => 'Health Score Lebih Tinggi',
                'value' => ($healthDiff >= 0 ? '+' : '') . $healthDiff,
                'unit' => 'poin',
                'description' => 'Early waker memiliki rata-rata health score ' . abs($healthDiff) . ' poin lebih ' . ($healthDiff >= 0 ? 'tinggi' : 'rendah') . ' dibandingkan non-early waker.',
                'direction' => $healthDiff >= 0 ? 'up' : 'down',
            ],
            [
                'title' => 'Tingkat Stres Lebih Rendah',
                'value' => ($stressDiff >= 0 ? '-' : '+') . abs($stressDiff),
                'unit' => 'poin',
                'description' => 'Early waker cenderung memiliki stress level ' . abs($stressDiff) . ' poin lebih rendah, mengindikasikan keseimbangan mental yang lebih baik.',
                'direction' => $stressDiff >= 0 ? 'down' : 'up',
            ],
            [
                'title' => 'Proporsi Wellness Excellent',
                'value' => $earlyExcPct . '%',
                'unit' => 'vs ' . $nonEarlyExcPct . '%',
                'description' => $earlyExcPct . '% early waker berada di kategori Excellent, dibandingkan ' . $nonEarlyExcPct . '% pada non-early waker.',
                'direction' => $earlyExcPct >= $nonEarlyExcPct ? 'up' : 'down',
            ],
        ];
    }

    /**
     * Get filter options (for dropdowns)
     */
    public function getFilterOptions(): array
    {
        return [
            'countries' => HealthData::distinct()->pluck('country')->sort()->values()->toArray(),
            'genders' => HealthData::distinct()->pluck('gender')->sort()->values()->toArray(),
            'wellness_categories' => ['Excellent', 'Good', 'Average', 'Poor'],
            'age_groups' => ['18-30', '31-45', '46-60', '61+'],
            'early_waker' => ['Yes', 'No'],
        ];
    }

    /**
     * Get data table (paginated)
     */
    public function getDataTable(array $filters = [], int $limit = 20, int $offset = 0): array
    {
        $query = $this->applyFilters(HealthData::query(), $filters);

        $total = $query->count();
        $data = (clone $query)
            ->select([
                'person_id', 'age', 'gender', 'country', 'bmi',
                'sleep_duration_hours', 'health_score', 'wellness_category',
                'early_waker', 'fitness_level', 'stress_level', 'is_outlier',
            ])
            ->offset($offset)
            ->limit($limit)
            ->get()
            ->toArray();

        return [
            'total' => $total,
            'data' => $data,
        ];
    }

    /**
     * Apply filters to query
     */
    private function applyFilters($query, array $filters)
    {
        if (!empty($filters['country'])) {
            $query->where('country', $filters['country']);
        }
        if (!empty($filters['gender'])) {
            $query->where('gender', $filters['gender']);
        }
        if (!empty($filters['wellness_category'])) {
            $query->where('wellness_category', $filters['wellness_category']);
        }
        if (!empty($filters['age_group'])) {
            $query->byAgeGroup($filters['age_group']);
        }
        if (!empty($filters['early_waker'])) {
            $query->where('early_waker', $filters['early_waker']);
        }

        return $query;
    }
}
