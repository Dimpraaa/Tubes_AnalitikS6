<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ActionController extends Controller
{
    public function index()
    {
        // 1. Data for Campaign Manager (Option A - K-Means)
        $segments = DB::table('orders')
            ->select('cluster_label', DB::raw('COUNT(order_id) as total_customers'), DB::raw('SUM(total_pembayaran) as total_revenue'))
            ->whereNotNull('cluster_label')
            ->groupBy('cluster_label')
            ->get();

        // Rekomendasi promosi berdasarkan nama segmen
        $promoRecommendations = [
            'Budget Order' => 'Voucher Gratis Ongkir (Min. Belanja Rp 0)',
            'Standard Order' => 'Diskon 10% (Min. Belanja Rp 50.000)',
            'High Value Order' => 'Cashback 20% + Prioritas Pengiriman',
            'Bulk/Premium Order' => 'Layanan Khusus VIP & Harga Grosir Spesial'
        ];

        foreach ($segments as $segment) {
            $segment->recommendation = $promoRecommendations[$segment->cluster_label] ?? 'Diskon Spesial';
        }

        // 2. Data for High-Risk Order Mitigation (Option D - Random Forest)
        // Kita simulasikan mengambil 20 order terakhir (atau ambil sample acak untuk demonstrasi)
        $incomingOrders = DB::table('orders')
            ->orderBy('waktu_pesanan_dibuat', 'desc')
            ->limit(20)
            ->get();

        // Kita buat fungsi sederhana untuk menghitung "Risk Score" berdasarkan Random Forest insight
        // Asumsi dari E-Commerce umumnya: COD memiliki risiko tinggi. Pengiriman Hemat juga bisa lambat dan meningkatkan risiko batal.
        foreach ($incomingOrders as $order) {
            $riskScore = 0;
            $riskFactors = [];

            if (str_contains(strtolower($order->metode_pembayaran), 'cod')) {
                $riskScore += 60;
                $riskFactors[] = 'Pembayaran COD';
            }
            if (str_contains(strtolower($order->opsi_pengiriman), 'hemat')) {
                $riskScore += 20;
                $riskFactors[] = 'Opsi Pengiriman Hemat';
            }
            if ($order->total_qty > 5) {
                $riskScore += 10;
                $riskFactors[] = 'Kuantitas Besar';
            }

            $order->risk_score = $riskScore;
            $order->risk_factors = $riskFactors;
            
            if ($riskScore >= 60) {
                $order->risk_level = 'High Risk';
                $order->risk_color = '#ef4444'; // Merah
            } elseif ($riskScore >= 20) {
                $order->risk_level = 'Medium Risk';
                $order->risk_color = '#f59e0b'; // Kuning
            } else {
                $order->risk_level = 'Low Risk';
                $order->risk_color = '#10b981'; // Hijau
            }
        }

        // Urutkan order berdasarkan risiko tertinggi ke terendah
        $incomingOrders = $incomingOrders->sortByDesc('risk_score')->values();

        return view('actions', compact('segments', 'incomingOrders'));
    }
    
    public function sendPromo(Request $request)
    {
        // Simulasi pengiriman promo
        $cluster = $request->input('cluster');
        return redirect()->back()->with('success', "Promo berhasil di-broadcast ke seluruh pelanggan dalam segmen: {$cluster}!");
    }
}
