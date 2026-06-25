# 🎤 Skenario Demo Project Besar: Analitik E-Commerce

Berikut adalah draf skenario atau naskah presentasi yang bisa Anda gunakan saat mendemokan aplikasi ini kepada dosen atau penguji. Alur ini sudah disusun persis mengikuti 8 poin pada ketentuan proyek Anda.

---

### 1. Latar Belakang & Problem Statement
*(Layar menampilkan halaman utama **Dashboard**)*

"Halo semuanya. Pada tugas besar kali ini, saya membuat sebuah aplikasi **Analitik dan Visualisasi Data E-Commerce**. 
Latar belakang proyek ini adalah banyaknya pemilik bisnis online yang kebingungan mengapa performa penjualan mereka stagnan dan mengapa banyak pesanan yang tiba-tiba dibatalkan (Canceled) oleh pembeli. 

**Problem Statement:**
1. Bagaimana tren penjualan perusahaan saat ini?
2. Siapa sebenarnya segmen pelanggan kita dan bagaimana cara menawarkan promo yang tepat sasaran?
3. Apa faktor utama yang menyebabkan pembatalan pesanan yang merugikan perusahaan?

Oleh karena itu, *dashboard* ini diciptakan khusus untuk manajer toko atau divisi *marketing* agar mereka mendapatkan *insight* langsung dari data mentah."

---

### 2. Dataset yang Digunakan
"Data yang saya gunakan berasal dari file `e_commerce.csv`. Dataset ini pada awalnya memiliki **20.848 baris data transaksi**. Di dalamnya memuat berbagai kolom penting seperti ID Pesanan, Total Kuantitas (Qty), Opsi Pengiriman, Metode Pembayaran, hingga Status Pesanan (apakah Selesai atau Dibatalkan)."

---

### 3. Data Cleaning (Pembersihan Data)
*(Anda klik menu **Data Cleaning** di sidebar sebelah kiri)*

"Tentu saja data mentah tidak bisa langsung diolah. Oleh karena itu, saya melakukan *Data Cleaning* menggunakan bahasa Python (Pandas) di belakang layar.

Seperti yang bisa Bapak/Ibu lihat pada layar ini:
- **Missing Values:** Pada data awal, terdapat nyaris 20.000 sel yang kosong (*missing*), terutama pada kolom 'Alasan Pembatalan' dan 'Waktu Pesanan'. Semua itu telah saya bersihkan menggunakan teknik *imputasi* nilai atau menghapus baris yang datanya cacat (*dropna*).
- **Data Duplikat:** (Tunjukkan bagian tabel duplikat jika ada).
- **Before vs After:** Di bagian paling bawah halaman ini (Step 5), Bapak/Ibu bisa melihat cuplikan tabel data mentah (*Before*) yang sel kosongnya disorot warna merah, dibandingkan dengan tabel bersihnya (*After*). Setelah dibersihkan, data kita menyusut menjadi data bersih yang siap diolah sebanyak **18.868 baris**."

---

### 4. Exploratory Data Analysis (EDA)
*(Sambil masih berada di halaman Data Cleaning atau kembali ke Dashboard)*

"Pada tahap analisis eksploratif, saya menemukan beberapa hal mendasar. Rata-rata pelanggan membeli sekitar 1-2 barang per pesanan. Secara statistik, kita melihat ada pesanan-pesanan yang memiliki total pembayaran di atas rata-rata normal (Outliers), yang mengindikasikan adanya kelompok pembeli kelas atas atau *bulk buyer* (pembeli grosir)."

---

### 5. Visualisasi Data
*(Kembali ke halaman utama **Dashboard**)*

"Untuk tahapan Visualisasi Data, saya merancang *dashboard* ini agar bisa bercerita (*data storytelling*) melalui 4 jenis grafik utama:

1. **Line Chart (Tren Penjualan Bulanan):** 
*(Tunjuk grafik garis di kiri atas)*
Grafik ini tidak hanya sekadar menampilkan naik turunnya pendapatan. Di sini kita bisa menganalisis secara mendalam pada bulan-bulan apa saja *revenue* melonjak drastis (misalnya efek musim liburan atau promo besar), dan di bulan apa penjualan menurun. Hal ini membantu manajemen untuk mengatur strategi *restock* barang dan memusatkan anggaran promosi di bulan-bulan yang tepat.

2. **Bar Chart (Top 10 Kategori Produk):** 
*(Tunjuk grafik batang hijau di kanan atas)*
Melalui grafik ini, kita dengan cepat bisa mengidentifikasi 10 kategori produk yang menjadi "tulang punggung" (*cash cow*) perusahaan karena menyumbang *revenue* terbesar. Data ini sangat krusial bagi tim pengadaan barang untuk memastikan stok pada kategori tersebut tidak boleh sampai kosong demi menjaga kestabilan pendapatan.

3. **Donut Chart (Proporsi Metode Pembayaran):** 
*(Tunjuk grafik lingkaran di kiri bawah)*
Visualisasi ini membedah preferensi cara bayar pelanggan (COD, Transfer Bank, E-Wallet, dsb). Dari besarnya porsi potongan *donut chart* ini, kita bisa mengambil kesimpulan. Misalnya, jika potongan COD sangat besar, berarti masyarakat lebih suka bayar di tempat, namun perusahaan juga harus mewaspadai risiko kerugian logistik jika barang ditolak saat sampai.

4. **Grafik Kombinasi (K-Means Segment Profile):** 
*(Tunjuk grafik gabungan Batang dan Garis di tengah/kanan bawah)*
Ini adalah grafik bebas saya yang mengombinasikan visualisasi bisnis dengan *Machine Learning*. Jika sebelumnya hasil segmentasi K-Means berbentuk *scatter plot* yang rumit, saya mengubahnya menjadi grafik "Profil Segmen" yang sangat mudah dipahami.
Grafik batang biru menunjukkan Rata-rata Pengeluaran, sedangkan garis oranye menunjukkan Rata-rata Jumlah Barang yang dibeli oleh keempat kelompok pelanggan tersebut. Dari sini kita bisa langsung menyimpulkan sifat pembeli, misalnya segmen "High Value" itu pembeli kaya karena batangnya tinggi (pengeluaran besar) walau garisnya (kuantitas) biasa saja, sementara "Bulk Order" adalah grosir karena pengeluaran tinggi dan kuantitas sangat tinggi!"

---

### 6. Dashboard Interaktif (Laravel)
*(Tunjukkan fitur Interaktif: Klik Dropdown Filter di atas, pilih bulan atau provinsi tertentu, lalu tekan tombol **Filter**)*

"Aplikasi ini dibangun menggunakan framework **Laravel**. *Dashboard* ini sangat interaktif. Jika saya memilih bulan tertentu dari menu *dropdown* di atas ini, seluruh angka KPI (Total Revenue, Orders, Cancellation Rate) dan grafik di bawahnya akan secara otomatis berubah (*slicing*) menyesuaikan bulan yang dipilih. Kita juga bisa menyorot (hover) ke arah grafik untuk melihat angka pastinya."

---

### 7. Insight (Hasil Analisis)
"Berdasarkan visualisasi dan pemodelan *Machine Learning* yang saya tanam di aplikasi ini, ada 2 *insight* (penemuan) besar:
1. **Pola Pelanggan (Segmentasi):** Melalui algoritma K-Means (*Grafik Kombinasi*), ternyata pelanggan kita terbagi menjadi 4 klaster utama: Pembeli Hemat (*Budget*), Pembeli Standar, Pembeli Nilai Tinggi (*High Value*), dan Pembeli Grosir/Premium (*Bulk*).
2. **Faktor Pembatalan:** Melalui algoritma Random Forest (Lihat *Bar Chart* berwarna ungu di bawah), sistem menemukan bahwa alasan terkuat seseorang membatalkan pesanannya sangat dipengaruhi oleh **Total Pembayaran** dan **Opsi Pengiriman / Metode Pembayaran** (khususnya COD)."

---

### 8. Rekomendasi
*(Klik menu **Actionable Insights** (Simbol Petir) di sidebar sebelah kiri)*

"Bagian paling menarik dari *project* ini ada di sini. Berdasarkan *insight* di poin 7 tadi, saya merekomendasikan dua fitur tindakan nyata (*Actionable*) yang bisa diambil oleh perusahaan:

1. **Targeted Campaign Manager:** Karena kita tahu ada 4 klaster pelanggan, saya merekomendasikan agar *marketing* tidak lagi membagikan diskon secara merata. Melalui tabel ini, admin bisa langsung mengeklik tombol **'Kirim Promo'** untuk memberikan Diskon Ongkir khusus pelanggan *Budget*, atau memberikan *Cashback* Besar khusus untuk pelanggan Grosir/Premium.
2. **High-Risk Order Mitigation (Sistem Peringatan Dini):** Mengingat kita tahu faktor pembatalan dari Random Forest, saya merancang sebuah fitur *Early Warning System*. Setiap pesanan masuk yang totalnya besar tapi menggunakan pembayaran COD dan pengiriman lambat, sistem akan mendeteksinya sebagai **🔴 High Risk**. Rekomendasinya: Admin harus segera menelpon pelanggan tersebut untuk konfirmasi manual sebelum barang benar-benar dikirim, demi mencegah kerugian biaya pengiriman akibat pesanan fiktif."

---
*(Selesai dan tutup dengan ucapan terima kasih)*
