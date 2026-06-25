# Analitik & Visualisasi Data E-Commerce

Aplikasi Dashboard interaktif berbasis **Laravel** yang mengintegrasikan Analitik Data dan Machine Learning (Python) untuk membedah performa penjualan dan perilaku pelanggan E-Commerce.

Proyek ini disusun sebagai **Tugas Besar Analitik & Visualisasi Data** (Semester 6).

---

## Fitur Utama

Aplikasi ini dibagi menjadi 3 halaman utama:

### 1. Dashboard (Interactive Visualizations)
Halaman utama yang menampilkan ringkasan data penjualan dengan grafik interaktif menggunakan **ApexCharts**.
*   **KPI Cards:** Total Revenue, Total Orders, dan Cancellation Rate.
*   **Line Chart:** Tren fluktuasi pendapatan bulanan.
*   **Bar Chart:** Top 10 kategori produk penyumbang revenue tertinggi.
*   **Donut Chart:** Proporsi perbandingan metode pembayaran.
*   **Grafik Kombinasi (Bar & Line):** Profil segmen pelanggan berdasarkan algoritma *K-Means Clustering*.
*   **Filter Interaktif:** Menyortir seluruh data di atas berdasarkan Bulan atau Provinsi.
*   **Cetak PDF:** Fitur *direct download* dashboard menjadi dokumen laporan PDF yang siap diserahkan ke manajemen.

### 2. Data Cleaning
Halaman dokumentasi transparansi pra-pemrosesan data.
*   Laporan deteksi dan penanganan *Missing Values* & Data Duplikat.
*   Tabel komparasi dataset mentah (*Before*) melawan dataset bersih (*After*) yang menyorot sel-sel bermasalah secara otomatis.

### 3. Actionable Insights
Sistem rekomendasi bisnis cerdas berdasarkan pemodelan *Machine Learning*:
*   **Targeted Campaign Manager (K-Means):** Rekomendasi promosi otomatis berdasarkan klasifikasi daya beli pelanggan (Budget, Standard, High Value, Bulk).
*   **High-Risk Order Mitigation (Random Forest):** Sistem Peringatan Dini (*Early Warning System*) untuk mendeteksi pesanan yang rawan dibatalkan, khususnya pada pengguna COD dengan nilai transaksi besar.

---

## Teknologi yang Digunakan

*   **Backend & Framework:** Laravel 11, PHP 8.2
*   **Frontend:** Blade Templating, Vanilla CSS, Feather Icons
*   **Data Visualization:** ApexCharts.js
*   **Machine Learning (Data Processing):** Python (Pandas, Scikit-Learn) -> Diekspor ke format JSON

---

## Instalasi & Menjalankan Proyek

1. **Clone repositori ini:**
   ```bash
   git clone https://github.com/Dimpraaa/Tubes_AnalitikS6.git
   cd Tubes_AnalitikS6/tb_visualisasi
   ```

2. **Install dependensi PHP/Laravel:**
   ```bash
   composer install
   ```

3. **Salin file .env dan generate App Key:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Konfigurasi Database:**
   Pastikan Anda mengatur konfigurasi koneksi database di file `.env`. (Contoh: menggunakan MySQL).
   
5. **Jalankan Migrasi & Seeder:**
   Karena aplikasi ini membaca data dari database, jalankan perintah berikut untuk mengimpor data transaksi mentah (*e_commerce.csv*) ke dalam tabel `orders`.
   ```bash
   php artisan migrate:fresh --seed
   ```

6. **Jalankan Local Development Server:**
   ```bash
   php artisan serve
   ```
   Akses `http://localhost:8000` di browser Anda.

---

## Struktur File Penting

*   `app/Http/Controllers/DashboardController.php`: Logika pemrosesan SQL dinamis untuk filter dan visualisasi chart.
*   `resources/views/`: Berisi seluruh tampilan halaman (`dashboard.blade.php`, `data-cleaning.blade.php`, `actions.blade.php`).
*   `python/`: Menyimpan *script* Google Colab/Jupyter Notebook (`data_cleaning.ipynb`, `machine_learning.py`) yang memproses ML dan mengekspor hasilnya ke format JSON.
*   `public/data/`: Tempat tersimpannya file hasil *Machine Learning* (`cleaning_report.json` dan `rf_feature_importance.json`) yang akan dibaca oleh Laravel.
*   `Skenario_Presentasi.md`: Naskah presentasi tugas besar yang memetakan seluruh ketentuan penilaian proyek.

---

*Dibuat untuk memenuhi Tugas Besar Mata Kuliah Analitik & Visualisasi Data - Semester 6.*
