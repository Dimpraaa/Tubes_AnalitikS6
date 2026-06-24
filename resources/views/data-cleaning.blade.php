@extends('layouts.app')

@section('title', 'Data Cleaning — Health Analytics')

@section('content')
    {{-- PAGE HEADER --}}
    <div class="page-header">
        <h1>Data Cleaning Report</h1>
        <p>Hasil pembersihan data menggunakan Python (pandas + numpy) — Algoritma IQR</p>
    </div>

    @if($cleaningReport)
    {{-- OVERVIEW CARDS --}}
    <div class="summary-grid">
        <div class="summary-card animate-in">
            <div class="icon-wrap"><i data-feather="database"></i></div>
            <div class="label">Data Awal</div>
            <div class="value">{{ number_format($cleaningReport['original_shape']['rows']) }}</div>
        </div>
        <div class="summary-card animate-in">
            <div class="icon-wrap"><i data-feather="check-circle"></i></div>
            <div class="label">Data Setelah Cleaning</div>
            <div class="value">{{ number_format($cleaningReport['final_shape']['rows']) }}</div>
        </div>
        <div class="summary-card animate-in">
            <div class="icon-wrap"><i data-feather="alert-circle"></i></div>
            <div class="label">Missing Values Fixed</div>
            <div class="value">{{ number_format($cleaningReport['missing_values']['resolved']) }}</div>
        </div>
        <div class="summary-card animate-in">
            <div class="icon-wrap"><i data-feather="copy"></i></div>
            <div class="label">Duplikat Dihapus</div>
            <div class="value">{{ $cleaningReport['duplicates']['removed'] }}</div>
        </div>
        <div class="summary-card animate-in">
            <div class="icon-wrap"><i data-feather="trending-up"></i></div>
            <div class="label">Outliers Terdeteksi</div>
            <div class="value">{{ number_format($cleaningReport['outliers']['total_outlier_rows']) }}</div>
        </div>
        <div class="summary-card animate-in">
            <div class="icon-wrap"><i data-feather="tool"></i></div>
            <div class="label">Consistency Fixes</div>
            <div class="value">{{ number_format($cleaningReport['consistency']['total_fixes']) }}</div>
        </div>
    </div>

    {{-- STEP 1: MISSING VALUES --}}
    <div class="card animate-in" style="margin-bottom: 20px;">
        <div class="card-title">
            <span style="color: var(--accent-teal);">Step 1</span> — Missing Value Handling
        </div>
        <p style="color: var(--text-secondary); font-size: 13px; margin-bottom: 16px;">
            Metode: <strong style="color: var(--accent-teal);">{{ $cleaningReport['missing_values']['method'] }}</strong>
        </p>

        <div class="before-after-grid">
            <div>
                <h3 style="color: var(--accent-rose); font-size: 14px; margin-bottom: 12px;">Before</h3>
                <div style="text-align: center; margin-bottom: 16px;">
                    <span style="font-size: 36px; font-weight: 700; color: var(--accent-rose);">{{ number_format($cleaningReport['missing_values']['before']['total']) }}</span>
                    <div style="font-size: 12px; color: var(--text-muted);">Missing Values</div>
                </div>
                @if(!empty($cleaningReport['missing_values']['before']['per_column']))
                    @foreach($cleaningReport['missing_values']['before']['per_column'] as $col => $count)
                    <div class="stat-row">
                        <span class="stat-label">{{ $col }}</span>
                        <span class="stat-value" style="color: var(--accent-rose);">{{ number_format($count) }}</span>
                    </div>
                    @endforeach
                @endif
            </div>

            <div class="arrow-divider">→</div>

            <div>
                <h3 style="color: var(--accent-emerald); font-size: 14px; margin-bottom: 12px;">After</h3>
                <div style="text-align: center; margin-bottom: 16px;">
                    <span style="font-size: 36px; font-weight: 700; color: var(--accent-emerald);">{{ number_format($cleaningReport['missing_values']['after']['total']) }}</span>
                    <div style="font-size: 12px; color: var(--text-muted);">Missing Values</div>
                </div>
                @if(!empty($cleaningReport['missing_values']['after']['per_column']))
                    @foreach($cleaningReport['missing_values']['after']['per_column'] as $col => $count)
                    <div class="stat-row">
                        <span class="stat-label">{{ $col }}</span>
                        <span class="stat-value" style="color: var(--accent-emerald);">{{ number_format($count) }}</span>
                    </div>
                    @endforeach
                @else
                    <div style="text-align: center; color: var(--accent-emerald); font-size: 13px;">
                        ✓ Semua missing values berhasil diimputasi
                    </div>
                @endif
            </div>
        </div>

        {{-- Missing Values Bar Chart --}}
        <div style="margin-top: 24px;">
            <div style="height: 250px;">
                <canvas id="missingValuesChart"></canvas>
            </div>
        </div>
    </div>

    {{-- STEP 2: DUPLICATES --}}
    <div class="card animate-in" style="margin-bottom: 20px;">
        <div class="card-title">
            <span style="color: var(--accent-teal);">Step 2</span> — Duplicate Detection
        </div>

        <div class="before-after-grid">
            <div style="text-align: center;">
                <div style="font-size: 36px; font-weight: 700; color: var(--text-primary);">{{ number_format($cleaningReport['duplicates']['before']) }}</div>
                <div style="font-size: 12px; color: var(--text-muted);">Rows (Before)</div>
            </div>
            <div class="arrow-divider">→</div>
            <div style="text-align: center;">
                <div style="font-size: 36px; font-weight: 700; color: var(--accent-emerald);">{{ number_format($cleaningReport['duplicates']['after']) }}</div>
                <div style="font-size: 12px; color: var(--text-muted);">Rows (After)</div>
            </div>
        </div>

        <div style="text-align: center; margin-top: 16px; padding: 16px; background: var(--bg-muted); border-radius: 10px;">
            @if($cleaningReport['duplicates']['removed'] > 0)
                <span style="color: var(--accent-amber); font-weight: 600;">
                    {{ $cleaningReport['duplicates']['removed'] }} duplikat dihapus berdasarkan Person_ID
                </span>
            @else
                <span style="color: var(--accent-emerald); font-weight: 600;">
                    ✓ Tidak ditemukan data duplikat
                </span>
            @endif
        </div>
    </div>

    {{-- STEP 3: OUTLIER DETECTION (IQR) --}}
    <div class="card animate-in" style="margin-bottom: 20px;">
        <div class="card-title">
            <span style="color: var(--accent-teal);">Step 3</span> — Outlier Detection (Algoritma IQR)
        </div>
        <p style="color: var(--text-secondary); font-size: 13px; margin-bottom: 8px;">
            Metode: <strong style="color: var(--accent-teal);">{{ $cleaningReport['outliers']['method'] }}</strong>
        </p>
        <p style="color: var(--text-muted); font-size: 12px; margin-bottom: 20px;">
            Outlier <strong>tidak dihapus</strong>, hanya ditandai (is_outlier = True) agar dapat dibandingkan dalam analisis.
        </p>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px;">
            <div style="text-align: center; padding: 20px; background: rgba(225, 29, 72, 0.06); border-radius: 12px;">
                <div style="font-size: 32px; font-weight: 700; color: var(--accent-rose);">{{ number_format($cleaningReport['outliers']['total_outlier_rows']) }}</div>
                <div style="font-size: 12px; color: var(--text-muted);">Rows dengan Outlier</div>
            </div>
            <div style="text-align: center; padding: 20px; background: rgba(5, 150, 105, 0.08); border-radius: 12px;">
                <div style="font-size: 32px; font-weight: 700; color: var(--accent-emerald);">{{ number_format($cleaningReport['outliers']['total_clean_rows']) }}</div>
                <div style="font-size: 12px; color: var(--text-muted);">Rows Bersih</div>
            </div>
        </div>

        {{-- IQR Detail per Column --}}
        @foreach($cleaningReport['outliers']['per_column'] as $colName => $colData)
        <div class="iqr-detail">
            <div class="col-name">
                {{ $colName }}
                <span style="font-size: 12px; font-weight: 400; color: var(--accent-rose); margin-left: 8px;">
                    {{ $colData['outlier_count'] }} outliers ({{ $colData['outlier_percentage'] }}%)
                </span>
            </div>
            <div class="iqr-values">
                <div class="iqr-val">
                    <div class="num">{{ $colData['Q1'] }}</div>
                    <div class="lbl">Q1</div>
                </div>
                <div class="iqr-val">
                    <div class="num">{{ $colData['Q3'] }}</div>
                    <div class="lbl">Q3</div>
                </div>
                <div class="iqr-val">
                    <div class="num">{{ $colData['IQR'] }}</div>
                    <div class="lbl">IQR</div>
                </div>
                <div class="iqr-val">
                    <div class="num">{{ $colData['lower_bound'] }}</div>
                    <div class="lbl">Lower</div>
                </div>
                <div class="iqr-val">
                    <div class="num">{{ $colData['upper_bound'] }}</div>
                    <div class="lbl">Upper</div>
                </div>
                <div class="iqr-val" style="background: var(--accent-rose-dim);">
                    <div class="num" style="color: var(--accent-rose);">{{ $colData['outlier_count'] }}</div>
                    <div class="lbl">Outliers</div>
                </div>
            </div>
        </div>
        @endforeach

        {{-- Outlier Chart --}}
        <div style="margin-top: 20px; height: 280px;">
            <canvas id="outlierChart"></canvas>
        </div>
    </div>

    {{-- STEP 4: CONSISTENCY --}}
    <div class="card animate-in" style="margin-bottom: 20px;">
        <div class="card-title">
            <span style="color: var(--accent-teal);">Step 4</span> — Data Consistency Validation
        </div>
        <div style="text-align: center; padding: 24px; background: var(--bg-muted); border-radius: 12px;">
            <div style="font-size: 32px; font-weight: 700; color: var(--accent-amber);">{{ number_format($cleaningReport['consistency']['total_fixes']) }}</div>
            <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">Total Consistency Fixes</div>
        </div>
        <div style="margin-top: 16px;">
            <div class="stat-row">
                <span class="stat-label">Kategori Normalisasi</span>
                <span class="stat-value" style="color: var(--accent-emerald);">
                    {{ $cleaningReport['consistency']['categories_normalized'] ? '✓ Selesai' : '✗ Belum' }}
                </span>
            </div>
            @if(!empty($cleaningReport['consistency']['out_of_range']))
                @foreach($cleaningReport['consistency']['out_of_range'] as $col => $count)
                <div class="stat-row">
                    <span class="stat-label">{{ $col }} (out of range → clipped)</span>
                    <span class="stat-value" style="color: var(--accent-amber);">{{ $count }}</span>
                </div>
                @endforeach
            @else
                <div class="stat-row">
                    <span class="stat-label">Out of Range Values</span>
                    <span class="stat-value" style="color: var(--accent-emerald);">0</span>
                </div>
            @endif
        </div>
    </div>

    {{-- STEP 5: DESCRIPTIVE STATISTICS --}}
    @if(isset($cleaningReport['statistics']))
    <div class="card animate-in" style="margin-bottom: 20px;">
        <div class="card-title">
            <span style="color: var(--accent-teal);">Step 5</span> — Statistik Deskriptif (Data Setelah Cleaning)
        </div>

        <div class="data-table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Kolom</th>
                        <th>Mean</th>
                        <th>Median</th>
                        <th>Std Dev</th>
                        <th>Min</th>
                        <th>Max</th>
                        <th>Q1</th>
                        <th>Q3</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cleaningReport['statistics']['numeric'] as $col => $stats)
                    <tr>
                        <td style="color: var(--accent-teal); font-weight: 600;">{{ $col }}</td>
                        <td>{{ $stats['mean'] }}</td>
                        <td>{{ $stats['median'] }}</td>
                        <td>{{ $stats['std'] }}</td>
                        <td>{{ $stats['min'] }}</td>
                        <td>{{ $stats['max'] }}</td>
                        <td>{{ $stats['q1'] }}</td>
                        <td>{{ $stats['q3'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Category Distributions --}}
        @if(isset($cleaningReport['statistics']['categorical']))
        <div style="margin-top: 24px;">
            <h3 style="font-size: 14px; color: var(--text-secondary); margin-bottom: 16px;">Distribusi Kategorikal</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 16px;">
                @foreach($cleaningReport['statistics']['categorical'] as $catCol => $dist)
                <div style="background: var(--bg-muted); border-radius: 10px; padding: 16px;">
                    <div style="font-size: 13px; font-weight: 600; color: var(--accent-blue); margin-bottom: 10px;">{{ $catCol }}</div>
                    @foreach($dist as $val => $count)
                    <div style="display: flex; justify-content: space-between; padding: 4px 0; font-size: 13px;">
                        <span style="color: var(--text-secondary);">{{ $val }}</span>
                        <span style="color: var(--text-primary); font-weight: 500;">{{ number_format($count) }} <span style="color: var(--text-muted); font-size: 11px;">({{ round($count / $cleaningReport['statistics']['total_rows'] * 100, 1) }}%)</span></span>
                    </div>
                    @endforeach
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
    @endif

    @else
    <div class="card" style="text-align: center; padding: 60px 24px;">
        <i data-feather="alert-triangle" style="width: 48px; height: 48px; color: var(--accent-amber); margin-bottom: 16px;"></i>
        <h2 style="font-size: 18px; margin-bottom: 8px;">Cleaning Report Belum Tersedia</h2>
        <p style="color: var(--text-secondary);">Jalankan Python data cleaning script terlebih dahulu:</p>
        <code style="display: inline-block; margin-top: 12px; padding: 12px 20px; background: rgba(255,255,255,0.05); border-radius: 8px; color: var(--accent-teal);">
            python python/data_cleaning.py
        </code>
    </div>
    @endif
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    @if($cleaningReport)
    // Missing Values Chart
    const missingData = @json($cleaningReport['missing_values']['before']['per_column']);
    const missingLabels = Object.keys(missingData);
    const missingValues = Object.values(missingData);

    if (missingLabels.length > 0) {
        new Chart(document.getElementById('missingValuesChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: missingLabels,
                datasets: [
                    {
                        label: 'Before Cleaning',
                        data: missingValues,
                        backgroundColor: 'rgba(251, 113, 133, 0.7)',
                        borderRadius: 6,
                    },
                    {
                        label: 'After Cleaning',
                        data: missingLabels.map(() => 0),
                        backgroundColor: 'rgba(52, 211, 153, 0.7)',
                        borderRadius: 6,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'top' } },
                scales: {
                    y: { grid: { color: 'rgba(0,0,0,0.05)' } },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    // Outlier Chart
    const outlierData = @json($cleaningReport['outliers']['per_column']);
    const outlierLabels = Object.keys(outlierData);
    const outlierCounts = outlierLabels.map(k => outlierData[k].outlier_count);
    const outlierPcts = outlierLabels.map(k => outlierData[k].outlier_percentage);

    new Chart(document.getElementById('outlierChart').getContext('2d'), {
        type: 'bar',
        data: {
            labels: outlierLabels,
            datasets: [{
                label: 'Outliers Detected',
                data: outlierCounts,
                backgroundColor: 'rgba(251, 113, 133, 0.6)',
                borderColor: 'rgba(251, 113, 133, 0.9)',
                borderWidth: 1,
                borderRadius: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        afterLabel: function(context) {
                            return outlierPcts[context.dataIndex] + '% of total data';
                        }
                    }
                }
            },
            scales: {
                y: { grid: { color: 'rgba(0,0,0,0.05)' } },
                x: { grid: { display: false } }
            }
        }
    });
    @endif
});
</script>
@endsection
