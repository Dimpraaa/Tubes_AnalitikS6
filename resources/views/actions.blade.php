@extends('layouts.app')

@section('title', 'Actionable Insights')

@section('content')
<div style="margin-bottom: 24px;">
    <h2 style="font-size: 24px; font-weight: 700; color: #0f172a;">Actionable Insights</h2>
    <p style="color: #64748b; margin-top: 4px;">Penerapan Machine Learning ke dalam aksi bisnis nyata (Targeted Campaign & Risk Mitigation).</p>
</div>

@if(session('success'))
<div style="background: #10b981; color: white; padding: 12px 16px; border-radius: 8px; margin-bottom: 24px; font-weight: 500;">
    ✅ {{ session('success') }}
</div>
@endif

{{-- OPTION A: TARGETED CAMPAIGN MANAGER --}}
<div class="card" style="margin-bottom: 32px;">
    <h3 class="card-title" style="display: flex; align-items: center; gap: 8px;">
        <i data-feather="gift" style="color: #3b82f6;"></i> 
        Targeted Campaign Manager (K-Means Output)
    </h3>
    <p style="font-size: 13px; color: #64748b; margin-bottom: 20px;">
        Kirimkan promosi spesifik berdasarkan klaster/segmentasi pelanggan. Pelanggan pada segmen yang sama memiliki pola belanja yang mirip.
    </p>

    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="border-bottom: 2px solid #e2e8f0;">
                    <th style="padding: 12px; color: #475569; font-size: 13px;">Segmen / Klaster</th>
                    <th style="padding: 12px; color: #475569; font-size: 13px;">Total Pesanan</th>
                    <th style="padding: 12px; color: #475569; font-size: 13px;">Total Revenue Segmen</th>
                    <th style="padding: 12px; color: #475569; font-size: 13px;">Rekomendasi Promosi</th>
                    <th style="padding: 12px; color: #475569; font-size: 13px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($segments as $seg)
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 12px; font-weight: 600; color: #0f172a;">{{ $seg->cluster_label }}</td>
                    <td style="padding: 12px; color: #475569;">{{ number_format($seg->total_customers) }} Orders</td>
                    <td style="padding: 12px; color: #10b981; font-weight: 500;">Rp {{ number_format($seg->total_revenue, 0, ',', '.') }}</td>
                    <td style="padding: 12px;">
                        <span style="background: #eff6ff; color: #2563eb; padding: 4px 10px; border-radius: 99px; font-size: 12px; font-weight: 500;">
                            {{ $seg->recommendation }}
                        </span>
                    </td>
                    <td style="padding: 12px;">
                        <form action="{{ route('actions.promo') }}" method="POST">
                            @csrf
                            <input type="hidden" name="cluster" value="{{ $seg->cluster_label }}">
                            <button type="submit" class="btn" style="background: #3b82f6; color: white; border: none; padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 12px; font-weight: 500;">
                                Kirim Promo
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- OPTION D: RISK MITIGATION --}}
<div class="card">
    <h3 class="card-title" style="display: flex; align-items: center; gap: 8px;">
        <i data-feather="alert-triangle" style="color: #ef4444;"></i> 
        High-Risk Order Mitigation (Random Forest Output)
    </h3>
    <p style="font-size: 13px; color: #64748b; margin-bottom: 20px;">
        Sistem Peringatan Dini. Fitur ini secara otomatis mensimulasikan deteksi pesanan masuk yang memiliki potensi batal tertinggi berdasarkan faktor Random Forest (seperti Pembayaran COD dan Pengiriman Hemat).
    </p>

    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="border-bottom: 2px solid #e2e8f0;">
                    <th style="padding: 12px; color: #475569; font-size: 13px;">Order ID</th>
                    <th style="padding: 12px; color: #475569; font-size: 13px;">Pembayaran & Pengiriman</th>
                    <th style="padding: 12px; color: #475569; font-size: 13px;">Qty & Total</th>
                    <th style="padding: 12px; color: #475569; font-size: 13px;">Risk Score</th>
                    <th style="padding: 12px; color: #475569; font-size: 13px;">Status Peringatan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($incomingOrders as $order)
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 12px; font-weight: 500; color: #0f172a; font-size: 13px;">{{ $order->order_id }}</td>
                    <td style="padding: 12px; font-size: 13px; color: #475569;">
                        <div><i data-feather="credit-card" style="width: 12px; height: 12px;"></i> {{ $order->metode_pembayaran }}</div>
                        <div style="margin-top: 4px;"><i data-feather="truck" style="width: 12px; height: 12px;"></i> {{ $order->opsi_pengiriman }}</div>
                    </td>
                    <td style="padding: 12px; font-size: 13px; color: #475569;">
                        {{ $order->total_qty }} pcs<br>
                        <strong>Rp {{ number_format($order->total_pembayaran, 0, ',', '.') }}</strong>
                    </td>
                    <td style="padding: 12px; font-size: 13px;">
                        <span style="font-weight: 700; color: {{ $order->risk_color }};">{{ $order->risk_score }} / 100</span>
                    </td>
                    <td style="padding: 12px;">
                        <span style="display: inline-block; background: {{ $order->risk_color }}15; color: {{ $order->risk_color }}; padding: 4px 10px; border-radius: 99px; font-size: 12px; font-weight: 600;">
                            {{ $order->risk_level }}
                        </span>
                        @if(count($order->risk_factors) > 0)
                            <div style="font-size: 11px; color: #64748b; margin-top: 6px;">
                                Faktor: {{ implode(', ', $order->risk_factors) }}
                            </div>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection

@section('scripts')
<script>
    // Inisialisasi ikon feather
    if(typeof feather !== 'undefined') {
        feather.replace();
    }
</script>
@endsection
