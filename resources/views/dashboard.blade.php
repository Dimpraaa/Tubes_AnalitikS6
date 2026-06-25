@extends('layouts.app')

@section('title', 'E-Commerce Dashboard')

@section('content')
<div id="dashboard-content">
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
    <div>
        <h2 style="font-size: 24px; font-weight: 700; color: #0f172a;">E-Commerce Sales Analytics</h2>
        <p style="color: #64748b; margin-top: 4px;">Analisis Performa Penjualan & Perilaku Pelanggan</p>
    </div>
    
    <form action="/" method="GET" style="display: flex; gap: 12px; align-items: center;">
        <select name="month" class="form-select">
            <option value="">Semua Bulan</option>
            @foreach($months as $m)
                <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>{{ $m }}</option>
            @endforeach
        </select>
        
        <select name="provinsi" class="form-select">
            <option value="">Semua Provinsi</option>
            @foreach($provinces as $p)
                <option value="{{ $p }}" {{ request('provinsi') == $p ? 'selected' : '' }}>{{ $p }}</option>
            @endforeach
        </select>
        
        <button type="submit" class="btn no-print">Filter</button>
        <a href="/" class="btn no-print" style="background: #e2e8f0; color: #475569; text-decoration: none;">Reset</a>
        <button type="button" onclick="generatePDF()" class="btn no-print" style="background: #10b981; display: flex; align-items: center; gap: 6px;">
            <i data-feather="download" style="width: 16px; height: 16px;"></i> Unduh PDF
        </button>
    </form>
</div>

{{-- KPI CARDS --}}
<div class="grid-4" style="margin-bottom: 24px;">
    <div class="card" style="margin-bottom: 0;">
        <p style="color: #64748b; font-size: 14px; font-weight: 600;">Total Revenue</p>
        <h3 style="font-size: 28px; font-weight: 700; color: #0f172a; margin-top: 8px;">Rp {{ number_format($summary['total_revenue'], 0, ',', '.') }}</h3>
    </div>
    <div class="card" style="margin-bottom: 0;">
        <p style="color: #64748b; font-size: 14px; font-weight: 600;">Total Orders</p>
        <h3 style="font-size: 28px; font-weight: 700; color: #0f172a; margin-top: 8px;">{{ number_format($summary['total_orders']) }}</h3>
    </div>
    <div class="card" style="margin-bottom: 0;">
        <p style="color: #64748b; font-size: 14px; font-weight: 600;">Orders Canceled</p>
        <h3 style="font-size: 28px; font-weight: 700; color: #ef4444; margin-top: 8px;">{{ number_format($summary['total_canceled']) }}</h3>
    </div>
    <div class="card" style="margin-bottom: 0;">
        <p style="color: #64748b; font-size: 14px; font-weight: 600;">Cancellation Rate</p>
        <h3 style="font-size: 28px; font-weight: 700; color: #f59e0b; margin-top: 8px;">{{ $summary['cancellation_rate'] }}%</h3>
    </div>
</div>

{{-- CHARTS ROW 1 --}}
<div class="grid-2">
    <div class="card">
        <h3 class="card-title">Tren Penjualan (Bulanan)</h3>
        <div id="lineChart"></div>
    </div>
    <div class="card">
        <h3 class="card-title">Top 10 Kategori Produk (By Revenue)</h3>
        <div id="barChart"></div>
    </div>
</div>

{{-- CHARTS ROW 2 --}}
<div class="grid-2">
    <div class="card">
        <h3 class="card-title">Proporsi Metode Pembayaran</h3>
        <div id="donutChart"></div>
    </div>
    <div class="card">
        <h3 class="card-title">K-Means: Profil Segmen Pelanggan</h3>
        <p style="font-size: 12px; color: #94a3b8; margin-bottom: 12px;">Perbandingan Rata-rata Pengeluaran (Batang) vs Kuantitas Barang (Garis) dari ke-4 klaster pelanggan.</p>
        <div id="segmentChart"></div>
    </div>
</div>

{{-- CHARTS ROW 3 (Random Forest) --}}
<div class="card">
    <h3 class="card-title">Random Forest: Faktor Utama Pembatalan Pesanan</h3>
    <p style="font-size: 12px; color: #94a3b8; margin-bottom: 12px;">Feature Importance dari algoritma Random Forest untuk mendeteksi penyebab utama pesanan dibatalkan.</p>
    <div id="rfChart"></div>
</div>

</div>
@endsection

@section('scripts')
<script>
    // PDF Generation
    function generatePDF() {
        const element = document.getElementById('dashboard-content');
        const btn = document.querySelector('button[onclick="generatePDF()"]');
        const originalText = btn.innerHTML;
        
        // Change button state
        btn.innerHTML = '<i data-feather="loader" style="width: 16px; height: 16px;"></i> Memproses...';
        feather.replace();
        
        const noPrintElements = document.querySelectorAll('.no-print');
        noPrintElements.forEach(el => el.style.visibility = 'hidden'); // Use visibility to keep layout intact

        const opt = {
            margin:       0.3,
            filename:     'E-Commerce_Analytics_Report.pdf',
            image:        { type: 'jpeg', quality: 0.98 },
            html2canvas:  { scale: 2, useCORS: true },
            jsPDF:        { unit: 'in', format: 'a4', orientation: 'landscape' }
        };

        html2pdf().set(opt).from(element).save().then(() => {
            // Restore elements
            noPrintElements.forEach(el => el.style.visibility = 'visible');
            btn.innerHTML = originalText;
            feather.replace();
        });
    }

    // 1. Line Chart (Tren Penjualan)
    const lineData = @json($lineChart);
    const lineOptions = {
        series: [{
            name: 'Revenue (Rp)',
            data: lineData.map(item => item.revenue)
        }],
        chart: { type: 'area', height: 320, toolbar: { show: false } },
        colors: ['#3b82f6'],
        xaxis: { categories: lineData.map(item => item.month) },
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth' },
        yaxis: {
            labels: {
                formatter: (value) => { return "Rp " + (value / 1000000).toFixed(1) + "M" }
            }
        }
    };
    new ApexCharts(document.querySelector("#lineChart"), lineOptions).render();

    // 2. Bar Chart (Top Categories)
    const barData = @json($barChart);
    const barOptions = {
        series: [{
            name: 'Revenue (Rp)',
            data: barData.map(item => item.total_revenue)
        }],
        chart: { type: 'bar', height: 320, toolbar: { show: false } },
        colors: ['#10b981'],
        plotOptions: {
            bar: { borderRadius: 4, horizontal: true }
        },
        dataLabels: { enabled: false },
        xaxis: { 
            categories: barData.map(item => item.category),
            labels: {
                formatter: function(val) {
                    if (val >= 1000000) return (val / 1000000).toFixed(1) + "M";
                    return val;
                }
            }
        },
        tooltip: {
            y: {
                formatter: function(val) {
                    return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                }
            }
        }
    };
    new ApexCharts(document.querySelector("#barChart"), barOptions).render();

    // 3. Donut Chart (Metode Pembayaran)
    const donutData = @json($donutChart);
    const donutOptions = {
        series: donutData.map(item => item.total),
        labels: donutData.map(item => item.metode_pembayaran),
        chart: { type: 'donut', height: 320 },
        colors: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#14b8a6', '#f97316'],
        plotOptions: { pie: { donut: { size: '60%' } } },
        legend: { position: 'bottom' }
    };
    new ApexCharts(document.querySelector("#donutChart"), donutOptions).render();

    // 4. Mixed Chart (K-Means Segment Profile)
    const segmentData = @json($segmentChart);
    const segmentOptions = {
        series: [
            { name: 'Rata-rata Pengeluaran (Rp)', type: 'column', data: segmentData.map(d => d.avg_spend) },
            { name: 'Rata-rata Kuantitas', type: 'line', data: segmentData.map(d => d.avg_qty) }
        ],
        chart: { type: 'line', height: 320, toolbar: { show: false } },
        colors: ['#3b82f6', '#f59e0b'],
        stroke: { width: [0, 4] },
        plotOptions: {
            bar: { borderRadius: 4, columnWidth: '50%' }
        },
        xaxis: { categories: segmentData.map(d => d.cluster_label) },
        yaxis: [
            { 
                title: { text: 'Pengeluaran (Rp)', style: { color: '#3b82f6' } }, 
                labels: { 
                    style: { colors: '#3b82f6' },
                    formatter: (val) => "Rp " + (val / 1000).toFixed(0) + "K" 
                } 
            },
            { 
                opposite: true, 
                title: { text: 'Kuantitas Barang', style: { color: '#f59e0b' } },
                labels: { 
                    style: { colors: '#f59e0b' },
                    formatter: (val) => val.toFixed(1) 
                }
            }
        ],
        legend: { position: 'top' },
        dataLabels: { enabled: false }
    };
    new ApexCharts(document.querySelector("#segmentChart"), segmentOptions).render();

    // 5. Bar Chart (Random Forest Feature Importance)
    const rfData = @json($featureImportance);
    if(rfData && rfData.features) {
        const rfOptions = {
            series: [{
                name: 'Importance Score',
                data: rfData.importances.map(val => Number(val.toFixed(4)))
            }],
            chart: { type: 'bar', height: 280, toolbar: { show: false } },
            colors: ['#8b5cf6'],
            plotOptions: {
                bar: { borderRadius: 4, horizontal: false, columnWidth: '40%' }
            },
            dataLabels: { enabled: true, formatter: function (val) { return val; } },
            xaxis: { categories: rfData.features }
        };
        new ApexCharts(document.querySelector("#rfChart"), rfOptions).render();
    }
</script>
@endsection
