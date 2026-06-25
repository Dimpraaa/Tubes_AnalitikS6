@extends('layouts.app')

@section('title', 'Data Cleaning — Health Analytics')

@section('content')
    @section('extra-styles')
    <style>
        .page-header { margin-bottom: 24px; }
        .page-header h1 { font-size: 24px; font-weight: 700; color: #0f172a; margin-bottom: 4px; }
        .page-header p { color: #64748b; font-size: 14px; }
        .summary-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px; }
        .summary-card { background: white; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
        .summary-card .icon-wrap { background: #eff6ff; padding: 12px; border-radius: 50%; color: #3b82f6; margin-bottom: 12px; }
        .summary-card .label { font-size: 13px; color: #64748b; margin-bottom: 4px; }
        .summary-card .value { font-size: 24px; font-weight: 700; color: #0f172a; }
        .before-after-grid { display: flex; align-items: center; justify-content: space-around; background: #f8fafc; padding: 24px; border-radius: 12px; margin-bottom: 16px; border: 1px dashed #cbd5e1; }
        .arrow-divider { font-size: 24px; color: #94a3b8; font-weight: bold; }
        .stat-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #f1f5f9; font-size: 13px; }
        .stat-label { color: #475569; }
        .stat-value { font-weight: 600; }
        .data-table-wrap { overflow-x: auto; margin-top: 16px; }
        .data-table { width: 100%; border-collapse: collapse; text-align: left; font-size: 13px; }
        .data-table th { background: #f8fafc; padding: 12px; color: #475569; border-bottom: 2px solid #e2e8f0; }
        .data-table td { padding: 12px; border-bottom: 1px solid #f1f5f9; color: #334155; }
    </style>
    @endsection

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
        @isset($cleaningReport['outliers'])
        <div class="summary-card animate-in">
            <div class="icon-wrap"><i data-feather="trending-up"></i></div>
            <div class="label">Outliers Terdeteksi</div>
            <div class="value">{{ number_format($cleaningReport['outliers']['total_outlier_rows']) }}</div>
        </div>
        @endisset
        @isset($cleaningReport['consistency'])
        <div class="summary-card animate-in">
            <div class="icon-wrap"><i data-feather="tool"></i></div>
            <div class="label">Consistency Fixes</div>
            <div class="value">{{ number_format($cleaningReport['consistency']['total_fixes']) }}</div>
        </div>
        @endisset
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
            <div id="missingValuesChart" style="min-height: 250px;"></div>
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

    @isset($cleaningReport['outliers'])
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
    @endisset

    @isset($cleaningReport['consistency'])
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
    @endisset

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

    @isset($cleaningReport['sample_before'])
    {{-- STEP 5: DATASET SAMPLES --}}
    <div class="card animate-in" style="margin-bottom: 20px;">
        <div class="card-title">
            <span style="color: var(--accent-teal);">Step 5</span> — Preview Dataset (Before vs After)
        </div>
        <p style="color: var(--text-secondary); font-size: 13px; margin-bottom: 20px;">
            Berikut adalah cuplikan 5 baris pertama dari data mentah sebelum dibersihkan dan sesudah dibersihkan.
        </p>

        {{-- BEFORE TABLE --}}
        <h3 style="font-size: 15px; color: var(--accent-rose); margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
            <i data-feather="x-circle" style="width: 18px; height: 18px;"></i> Before Cleaning (Mentah)
        </h3>
        <div class="data-table-wrap" style="margin-bottom: 32px; border: 1px solid #e2e8f0; border-radius: 8px;">
            <table class="data-table" style="white-space: nowrap;">
                <thead style="background: rgba(225, 29, 72, 0.05);">
                    <tr>
                        @foreach(array_keys($cleaningReport['sample_before'][0] ?? []) as $col)
                            <th style="padding: 10px; font-size: 12px; border-bottom: 2px solid #fecaca; color: #9f1239;">{{ $col }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($cleaningReport['sample_before'] as $row)
                    <tr>
                        @foreach($row as $val)
                            <td style="padding: 8px 10px; font-size: 12px; {{ $val === 'NaN' || $val === null || $val === '' ? 'background: #fee2e2; color: #dc2626; font-weight: bold;' : '' }}">
                                {{ $val === 'NaN' ? 'Missing' : $val }}
                            </td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- AFTER TABLE --}}
        <h3 style="font-size: 15px; color: var(--accent-emerald); margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
            <i data-feather="check-circle" style="width: 18px; height: 18px;"></i> After Cleaning (Bersih)
        </h3>
        <div class="data-table-wrap" style="border: 1px solid #e2e8f0; border-radius: 8px;">
            <table class="data-table" style="white-space: nowrap;">
                <thead style="background: rgba(5, 150, 105, 0.05);">
                    <tr>
                        @foreach(array_keys($cleaningReport['sample_after'][0] ?? []) as $col)
                            <th style="padding: 10px; font-size: 12px; border-bottom: 2px solid #a7f3d0; color: #065f46;">{{ $col }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($cleaningReport['sample_after'] as $row)
                    <tr>
                        @foreach($row as $val)
                            <td style="padding: 8px 10px; font-size: 12px;">{{ $val }}</td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endisset

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
    // Missing Values Chart (ApexCharts)
    const missingData = @json($cleaningReport['missing_values']['before']['per_column'] ?? []);
    const missingLabels = Object.keys(missingData);
    const missingValues = Object.values(missingData);

    if (missingLabels.length > 0) {
        var options = {
            series: [{
                name: 'Before Cleaning',
                data: missingValues
            }, {
                name: 'After Cleaning',
                data: missingLabels.map(() => 0)
            }],
            chart: {
                type: 'bar',
                height: 280,
                toolbar: { show: false }
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '55%',
                    endingShape: 'rounded',
                    borderRadius: 4
                },
            },
            dataLabels: { enabled: false },
            stroke: { show: true, width: 2, colors: ['transparent'] },
            xaxis: { categories: missingLabels },
            yaxis: { title: { text: 'Missing Values' } },
            fill: { opacity: 1 },
            colors: ['#fb7185', '#34d399']
        };

        var chart = new ApexCharts(document.querySelector("#missingValuesChart"), options);
        chart.render();
    }

    @isset($cleaningReport['outliers'])
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
    @endisset
    @endif
});
</script>
@endsection
