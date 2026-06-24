@extends('layouts.app')

@section('title', 'E-Commerce Dashboard')

@section('content')
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
        
        <button type="submit" class="btn">Filter</button>
        <a href="/" class="btn" style="background: #e2e8f0; color: #475569; text-decoration: none;">Reset</a>
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
        <h3 class="card-title">K-Means: Customer Segmentation (Qty vs Spend)</h3>
        <p style="font-size: 12px; color: #94a3b8; margin-bottom: 12px;">Visualisasi sampel 500 data pesanan dari hasil segmentasi K-Means.</p>
        <div id="scatterChart"></div>
    </div>
</div>

{{-- CHARTS ROW 3 (Random Forest) --}}
<div class="card">
    <h3 class="card-title">Random Forest: Faktor Utama Pembatalan Pesanan</h3>
    <p style="font-size: 12px; color: #94a3b8; margin-bottom: 12px;">Feature Importance dari algoritma Random Forest untuk mendeteksi penyebab utama pesanan dibatalkan.</p>
    <div id="rfChart"></div>
</div>

@endsection

@section('scripts')
<script>
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
        xaxis: { categories: barData.map(item => item.category) }
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

    // 4. Scatter Plot (K-Means)
    const scatterDataRaw = @json($scatterChart);
    const scatterSeries = Object.keys(scatterDataRaw).map(key => {
        return {
            name: key,
            data: scatterDataRaw[key]
        }
    });
    const scatterOptions = {
        series: scatterSeries,
        chart: { type: 'scatter', height: 320, zoom: { type: 'xy' } },
        colors: ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6'],
        xaxis: { 
            title: { text: 'Total Quantity' },
            tickAmount: 10
        },
        yaxis: { 
            title: { text: 'Total Pembayaran (Rp)' },
            labels: { formatter: (val) => "Rp " + (val / 1000).toFixed(0) + "K" }
        },
        legend: { position: 'top' }
    };
    new ApexCharts(document.querySelector("#scatterChart"), scatterOptions).render();

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
