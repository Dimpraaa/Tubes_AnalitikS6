import pandas as pd
import numpy as np
import json
import os
from IPython.display import display, HTML, Markdown
from sklearn.cluster import KMeans
from sklearn.preprocessing import StandardScaler, LabelEncoder
from sklearn.ensemble import RandomForestClassifier
from sklearn.impute import SimpleImputer

# Path konfigurasi
input_csv = "../e_commerce.csv"
output_dir = "output"
os.makedirs(output_dir, exist_ok=True)
output_csv = os.path.join(output_dir, "cleaned_ecommerce.csv")
output_rf_json = os.path.join(output_dir, "rf_feature_importance.json")
output_report_json = os.path.join(output_dir, "cleaning_report.json")

# Helper function untuk styling tabel
def styled_header(text, color='#3b82f6'):
    display(HTML(f'<h3 style="color:{color}; border-bottom: 2px solid {color}; padding-bottom: 8px; margin-top: 20px;">{text}</h3>'))

def highlight_missing(val):
    """Highlight missing values dengan warna merah"""
    if pd.isna(val) or val == '' or val is None:
        return 'background-color: #fecaca; color: #991b1b; font-weight: bold'
    return ''

print(f"Loading data from {input_csv}...")
df_raw = pd.read_csv(input_csv, delimiter=';')
print(f"✅ Data loaded successfully!")
print(f"📊 Shape: {df_raw.shape[0]} rows × {df_raw.shape[1]} columns")

# === BEFORE CLEANING: Preview Data Awal ===
styled_header('📄 Preview 10 Baris Pertama — Data Mentah (BEFORE)', '#ef4444')

# Tampilkan tabel dengan highlight missing values
display(
    df_raw.head(10)
    .style
    .applymap(highlight_missing)
    .set_caption('Data Mentah — Sel merah = Missing Value')
    .set_table_styles([
        {'selector': 'caption', 'props': [('font-size', '14px'), ('font-weight', 'bold'), ('color', '#dc2626')]},
        {'selector': 'th', 'props': [('background-color', '#1e293b'), ('color', 'white'), ('font-size', '12px'), ('padding', '8px')]},
        {'selector': 'td', 'props': [('font-size', '11px'), ('padding', '6px 8px')]},
    ])
)

# === BEFORE CLEANING: Info Data ===
styled_header('📊 Informasi Data — BEFORE Cleaning', '#ef4444')

# Buat tabel info missing values
before_info = pd.DataFrame({
    'Kolom': df_raw.columns,
    'Tipe Data': df_raw.dtypes.values,
    'Non-Null Count': df_raw.notna().sum().values,
    'Missing Count': df_raw.isna().sum().values,
    'Missing %': (df_raw.isna().sum().values / len(df_raw) * 100).round(2)
})

# Highlight kolom yang punya missing values
def highlight_missing_rows(row):
    if row['Missing Count'] > 0:
        return ['background-color: #fef2f2'] * len(row)
    return [''] * len(row)

display(
    before_info
    .style
    .apply(highlight_missing_rows, axis=1)
    .set_caption(f'Info Data Awal — Total: {df_raw.shape[0]} baris, {df_raw.shape[1]} kolom')
    .set_table_styles([
        {'selector': 'caption', 'props': [('font-size', '14px'), ('font-weight', 'bold')]},
        {'selector': 'th', 'props': [('background-color', '#1e293b'), ('color', 'white'), ('padding', '8px')]},
        {'selector': 'td', 'props': [('padding', '6px 8px')]},
    ])
    .hide(axis='index')
)

# Ringkasan missing values
total_missing_before = df_raw.isna().sum().sum()
cols_with_missing = df_raw.columns[df_raw.isna().any()].tolist()

print(f"\n🔴 Total Missing Values: {total_missing_before}")
print(f"🔴 Kolom dengan Missing: {cols_with_missing if cols_with_missing else 'Tidak ada'}")

# === BEFORE CLEANING: Statistik Deskriptif ===
styled_header('📈 Statistik Deskriptif — BEFORE Cleaning', '#ef4444')

before_stats = df_raw.describe(include='all').T
display(
    before_stats
    .style
    .set_caption('Statistik Deskriptif Data Mentah')
    .set_table_styles([
        {'selector': 'caption', 'props': [('font-size', '14px'), ('font-weight', 'bold')]},
        {'selector': 'th', 'props': [('background-color', '#1e293b'), ('color', 'white'), ('padding', '8px')]},
        {'selector': 'td', 'props': [('padding', '6px 8px'), ('font-size', '11px')]},
    ])
)

# Simpan bentuk awal untuk perbandingan
shape_before = df_raw.shape
missing_before_detail = df_raw.isna().sum()
missing_before_detail = missing_before_detail[missing_before_detail > 0]
duplicates_before = df_raw.duplicated().sum()

# Buat copy untuk dikerjakan
df = df_raw.copy()

styled_header('🔧 Proses Cleaning Dimulai...', '#f59e0b')

# --- Step 1: Menangani Missing Values ---
print("\n📌 Step 1: Menangani Missing Values")
print("="*50)

# 1. Alasan Pembatalan: Isi dengan 'Tidak Batal'
missing_alasan = df['Alasan Pembatalan'].isna().sum()
df['Alasan Pembatalan'] = df['Alasan Pembatalan'].fillna('Tidak Batal')
print(f"  ✅ 'Alasan Pembatalan': {missing_alasan} NaN → diisi 'Tidak Batal'")

# 2. Waktu Pesanan Dibuat: Konversi datetime
missing_waktu_before = df['Waktu Pesanan Dibuat'].isna().sum()
df['Waktu Pesanan Dibuat'] = pd.to_datetime(df['Waktu Pesanan Dibuat'], errors='coerce')
missing_waktu_coerced = df['Waktu Pesanan Dibuat'].isna().sum()

# Drop baris yang datetime-nya NaT karena krusial untuk time-series dashboard
rows_before_drop = len(df)
df = df.dropna(subset=['Waktu Pesanan Dibuat'])
rows_dropped = rows_before_drop - len(df)
print(f"  ✅ 'Waktu Pesanan Dibuat': {missing_waktu_before} NaN awal, {missing_waktu_coerced} setelah coerce")
print(f"     → {rows_dropped} baris di-drop karena datetime tidak valid")

# --- Step 2: Konversi Kolom Numerik ---
print("\n📌 Step 2: Konversi Kolom Numerik")
print("="*50)

numeric_cols = ['total_qty', 'total_weight_gr', 'total_returned_qty', 'Total Diskon', 
                'num_product_categories', 'Ongkos Kirim Dibayar oleh Pembeli', 
                'Estimasi Potongan Biaya Pengiriman', 'Total Pembayaran', 'Perkiraan Ongkos Kirim']

for col in numeric_cols:
    non_numeric_count = pd.to_numeric(df[col], errors='coerce').isna().sum() - df[col].isna().sum()
    df[col] = pd.to_numeric(df[col], errors='coerce').fillna(0)
    if non_numeric_count > 0:
        print(f"  ✅ '{col}': {non_numeric_count} nilai non-numerik dikonversi → 0")
    else:
        print(f"  ✅ '{col}': OK (sudah numerik)")

# --- Step 3: Duplikat ---
print("\n📌 Step 3: Pengecekan Duplikat")
print("="*50)
dupes = df.duplicated().sum()
if dupes > 0:
    df = df.drop_duplicates()
    print(f"  ✅ {dupes} baris duplikat dihapus")
else:
    print(f"  ✅ Tidak ditemukan baris duplikat")

print(f"\n✅ Cleaning selesai! Shape: {df.shape[0]} rows × {df.shape[1]} columns")

# === AFTER CLEANING: Preview Data Bersih ===
styled_header('📄 Preview 10 Baris Pertama — Data Bersih (AFTER)', '#10b981')

display(
    df.head(10)
    .style
    .applymap(highlight_missing)
    .set_caption('Data Setelah Cleaning — Tidak ada sel merah = Bersih!')
    .set_table_styles([
        {'selector': 'caption', 'props': [('font-size', '14px'), ('font-weight', 'bold'), ('color', '#059669')]},
        {'selector': 'th', 'props': [('background-color', '#064e3b'), ('color', 'white'), ('font-size', '12px'), ('padding', '8px')]},
        {'selector': 'td', 'props': [('font-size', '11px'), ('padding', '6px 8px')]},
    ])
)

# === AFTER CLEANING: Info Data ===
styled_header('📊 Informasi Data — AFTER Cleaning', '#10b981')

after_info = pd.DataFrame({
    'Kolom': df.columns,
    'Tipe Data': df.dtypes.values,
    'Non-Null Count': df.notna().sum().values,
    'Missing Count': df.isna().sum().values,
    'Missing %': (df.isna().sum().values / len(df) * 100).round(2)
})

display(
    after_info
    .style
    .set_caption(f'Info Data Bersih — Total: {df.shape[0]} baris, {df.shape[1]} kolom')
    .set_table_styles([
        {'selector': 'caption', 'props': [('font-size', '14px'), ('font-weight', 'bold'), ('color', '#059669')]},
        {'selector': 'th', 'props': [('background-color', '#064e3b'), ('color', 'white'), ('padding', '8px')]},
        {'selector': 'td', 'props': [('padding', '6px 8px')]},
    ])
    .hide(axis='index')
)

total_missing_after = df.isna().sum().sum()
print(f"\n🟢 Total Missing Values: {total_missing_after}")
print(f"🟢 Shape: {df.shape[0]} baris × {df.shape[1]} kolom")

# === AFTER CLEANING: Statistik Deskriptif ===
styled_header('📈 Statistik Deskriptif — AFTER Cleaning', '#10b981')

after_stats = df.describe(include='all').T
display(
    after_stats
    .style
    .set_caption('Statistik Deskriptif Data Bersih')
    .set_table_styles([
        {'selector': 'caption', 'props': [('font-size', '14px'), ('font-weight', 'bold'), ('color', '#059669')]},
        {'selector': 'th', 'props': [('background-color', '#064e3b'), ('color', 'white'), ('padding', '8px')]},
        {'selector': 'td', 'props': [('padding', '6px 8px'), ('font-size', '11px')]},
    ])
)

# === TABEL PERBANDINGAN BEFORE vs AFTER ===
styled_header('🔄 Ringkasan Perbandingan: Before vs After', '#3b82f6')

shape_after = df.shape
missing_after_total = df.isna().sum().sum()
duplicates_after = df.duplicated().sum()

comparison_data = {
    'Metrik': [
        'Jumlah Baris',
        'Jumlah Kolom', 
        'Total Missing Values',
        'Jumlah Duplikat',
        'Baris Dihapus',
        'Missing Values Diperbaiki',
    ],
    'BEFORE 🔴': [
        f"{shape_before[0]:,}",
        f"{shape_before[1]}",
        f"{total_missing_before:,}",
        f"{duplicates_before:,}",
        '—',
        '—',
    ],
    'AFTER 🟢': [
        f"{shape_after[0]:,}",
        f"{shape_after[1]}",
        f"{missing_after_total:,}",
        f"{duplicates_after:,}",
        f"{shape_before[0] - shape_after[0]:,}",
        f"{total_missing_before - missing_after_total:,}",
    ],
    'Status': [
        '✅' if shape_after[0] > 0 else '❌',
        '✅',
        '✅ Bersih!' if missing_after_total == 0 else f'⚠️ Masih ada {missing_after_total}',
        '✅ Bersih!' if duplicates_after == 0 else f'⚠️ Masih ada {duplicates_after}',
        '✅',
        '✅',
    ]
}

comparison_df = pd.DataFrame(comparison_data)

display(
    comparison_df
    .style
    .set_caption('Perbandingan Data: Before vs After Cleaning')
    .set_table_styles([
        {'selector': 'caption', 'props': [('font-size', '16px'), ('font-weight', 'bold'), ('color', '#1e40af'), ('padding', '12px')]},
        {'selector': 'th', 'props': [('background-color', '#1e3a5f'), ('color', 'white'), ('padding', '10px 14px'), ('font-size', '13px')]},
        {'selector': 'td', 'props': [('padding', '10px 14px'), ('font-size', '13px'), ('border-bottom', '1px solid #e2e8f0')]},
    ])
    .hide(axis='index')
)

# Tabel detail per-kolom Missing Values
if len(missing_before_detail) > 0:
    styled_header('📋 Detail Missing Values Per Kolom', '#3b82f6')
    
    missing_comparison = pd.DataFrame({
        'Kolom': missing_before_detail.index,
        'Missing BEFORE': missing_before_detail.values,
        '% BEFORE': (missing_before_detail.values / shape_before[0] * 100).round(2),
        'Missing AFTER': [df[col].isna().sum() if col in df.columns else 'N/A' for col in missing_before_detail.index],
        'Penanganan': [
            'Diisi "Tidak Batal"' if col == 'Alasan Pembatalan' 
            else 'Drop baris NaT' if col == 'Waktu Pesanan Dibuat'
            else 'to_numeric + fillna(0)' if col in numeric_cols
            else 'Otomatis'
            for col in missing_before_detail.index
        ]
    })
    
    display(
        missing_comparison
        .style
        .set_caption('Detail Penanganan Missing Values')
        .set_table_styles([
            {'selector': 'caption', 'props': [('font-size', '14px'), ('font-weight', 'bold')]},
            {'selector': 'th', 'props': [('background-color', '#1e3a5f'), ('color', 'white'), ('padding', '8px')]},
            {'selector': 'td', 'props': [('padding', '8px')]},
        ])
        .hide(axis='index')
    )
else:
    print("✅ Tidak ada missing values pada data awal.")

print("\n" + "="*60)
print("📊 KESIMPULAN CLEANING:")
print(f"   • Data awal: {shape_before[0]:,} baris → Data bersih: {shape_after[0]:,} baris")
print(f"   • Missing values: {total_missing_before:,} → {missing_after_total:,}")
print(f"   • Duplikat: {duplicates_before:,} → {duplicates_after:,}")
print("="*60)

# Kita akan mensegmentasi pesanan berdasarkan perilaku pembelian
cluster_features = ['total_qty', 'total_weight_gr', 'Total Pembayaran', 'Perkiraan Ongkos Kirim']
X_cluster = df[cluster_features].copy()

# Standarisasi data
scaler = StandardScaler()
X_scaled = scaler.fit_transform(X_cluster)

# K-Means dengan k=4 (misal: budget, standard, bulk, premium)
kmeans = KMeans(n_clusters=4, random_state=42, n_init=10)
df['Cluster'] = kmeans.fit_predict(X_scaled)

# Penamaan cluster berdasar rata-rata Total Pembayaran
cluster_means = df.groupby('Cluster')['Total Pembayaran'].mean().sort_values()
cluster_mapping = {
    cluster_means.index[0]: 'Budget Order',
    cluster_means.index[1]: 'Standard Order',
    cluster_means.index[2]: 'High Value Order',
    cluster_means.index[3]: 'Bulk/Premium Order'
}
df['Cluster_Label'] = df['Cluster'].map(cluster_mapping)

styled_header('🎯 Distribusi Cluster', '#8b5cf6')
cluster_dist = df['Cluster_Label'].value_counts().reset_index()
cluster_dist.columns = ['Cluster', 'Jumlah Pesanan']
cluster_dist['Persentase'] = (cluster_dist['Jumlah Pesanan'] / len(df) * 100).round(2).astype(str) + '%'

display(
    cluster_dist
    .style
    .set_caption('Segmentasi Pesanan (K-Means, k=4)')
    .set_table_styles([
        {'selector': 'caption', 'props': [('font-size', '14px'), ('font-weight', 'bold')]},
        {'selector': 'th', 'props': [('background-color', '#4c1d95'), ('color', 'white'), ('padding', '10px')]},
        {'selector': 'td', 'props': [('padding', '8px')]},
    ])
    .hide(axis='index')
)

# Membuat model untuk memprediksi apakah pesanan Batal
# Target: Batal (1) vs Tidak Batal (0)
df['Is_Batal'] = (df['Status Pesanan'] != 'Selesai').astype(int)

# Fitur yang digunakan
rf_features = ['total_qty', 'total_weight_gr', 'Total Pembayaran', 'Metode Pembayaran', 'Opsi Pengiriman', 'Provinsi']

# Siapkan data untuk Random Forest
X_rf = df[rf_features].copy()
y_rf = df['Is_Batal']

# Label Encoding untuk kolom kategorikal
le_dict = {}
cat_features = ['Metode Pembayaran', 'Opsi Pengiriman', 'Provinsi']
for col in cat_features:
    le = LabelEncoder()
    # Handle missing value di kategorikal (just in case)
    X_rf[col] = X_rf[col].fillna('Unknown')
    X_rf[col] = le.fit_transform(X_rf[col].astype(str))
    le_dict[col] = le

# Train Random Forest
rf = RandomForestClassifier(n_estimators=100, max_depth=10, random_state=42)
rf.fit(X_rf, y_rf)

# Ambil Feature Importance
importances = rf.feature_importances_
feature_importance_df = pd.DataFrame({
    'Feature': rf_features,
    'Importance': importances
}).sort_values('Importance', ascending=False)

styled_header('🌲 Feature Importance (Random Forest)', '#059669')

fi_display = feature_importance_df.copy()
fi_display['Importance'] = fi_display['Importance'].round(4)
fi_display['Bar'] = fi_display['Importance'].apply(
    lambda x: '█' * int(x * 50) + '░' * (50 - int(x * 50))
)

display(
    fi_display
    .style
    .set_caption('Faktor Penyebab Pembatalan Pesanan')
    .set_table_styles([
        {'selector': 'caption', 'props': [('font-size', '14px'), ('font-weight', 'bold')]},
        {'selector': 'th', 'props': [('background-color', '#064e3b'), ('color', 'white'), ('padding', '10px')]},
        {'selector': 'td', 'props': [('padding', '8px'), ('font-family', 'monospace')]},
    ])
    .hide(axis='index')
)

# Simpan ke JSON untuk Dashboard
rf_result = {
    'features': feature_importance_df['Feature'].tolist(),
    'importances': feature_importance_df['Importance'].tolist()
}
with open(output_rf_json, 'w') as f:
    json.dump(rf_result, f)
print(f"\n✅ Feature importance saved to {output_rf_json}")

# Export data yang sudah dibersihkan dan dilabeli cluster ke CSV
# Untuk Dashboard Laravel
# Format tanggal diseragamkan
df['Waktu Pesanan Dibuat'] = df['Waktu Pesanan Dibuat'].dt.strftime('%Y-%m-%d %H:%M:%S')

df.to_csv(output_csv, index=False)
print(f"✅ Cleaned dataset saved to {output_csv}")

# === Generate Cleaning Report JSON untuk Dashboard Laravel ===
cleaning_report = {
    'source_file': 'e_commerce.csv',
    'original_shape': {
        'rows': int(shape_before[0]),
        'columns': int(shape_before[1])
    },
    'missing_values': {
        'before': {
            'total': int(total_missing_before),
            'per_column': {col: int(val) for col, val in missing_before_detail.items()}
        },
        'after': {
            'total': int(missing_after_total),
            'per_column': {col: int(df[col].isna().sum()) for col in df.columns if df[col].isna().sum() > 0}
        },
        'resolved': int(total_missing_before - missing_after_total),
        'method': 'fillna (kategorikal), dropna (datetime), to_numeric + fillna(0) (numerik)'
    },
    'duplicates': {
        'before': int(shape_before[0]),
        'after': int(df.shape[0]),
        'removed': int(duplicates_before),
        'example_ids': []
    },
    'final_shape': {
        'rows': int(df.shape[0]),
        'columns': int(df.shape[1])
    },
    'sample_before': df_raw.head(5).fillna('NaN').to_dict(orient='records'),
    'sample_after': df.head(5).to_dict(orient='records')
}

with open(output_report_json, 'w') as f:
    json.dump(cleaning_report, f, indent=2, default=str)
print(f"✅ Cleaning report saved to {output_report_json}")

print("\n" + "="*60)
print("🎉 Pipeline selesai!")
print(f"   📁 Data bersih: {output_csv}")
print(f"   📁 Feature importance: {output_rf_json}")
print(f"   📁 Cleaning report: {output_report_json}")
print("="*60)
