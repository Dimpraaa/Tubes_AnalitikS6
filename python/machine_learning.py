import pandas as pd
import numpy as np
from sklearn.cluster import KMeans
from sklearn.preprocessing import StandardScaler
from mlxtend.preprocessing import TransactionEncoder
from mlxtend.frequent_patterns import apriori, association_rules
import json
import os

# Create output directory if it doesn't exist
output_dir = 'output'
if not os.path.exists(output_dir):
    os.makedirs(output_dir)

print("Membaca dataset...")
# Load the dataset
# Adjust path depending on where the script is executed
dataset_path = '../e_commerce.csv'
if not os.path.exists(dataset_path):
    dataset_path = 'e_commerce.csv'

df = pd.read_csv(dataset_path, sep=';')

# Fill or drop missing values
df = df.dropna(subset=['Total Pembayaran', 'total_qty', 'product_categories'])

# ==========================================
# 1. CLUSTERING (Segmentasi Pelanggan)
# ==========================================
print("\n[1] Menjalankan Clustering (K-Means)...")
clustering_features = ['Total Pembayaran', 'total_qty']

# Mengambil data untuk clustering
X = df[clustering_features].copy()

# Normalisasi data
scaler = StandardScaler()
X_scaled = scaler.fit_transform(X)

# Menjalankan K-Means dengan K=4 (misalnya: VIP, Mid-High, Mid-Low, Low)
kmeans = KMeans(n_clusters=4, random_state=42, n_init=10)
kmeans.fit(X_scaled)

# Menambahkan label cluster ke dataframe asli
df['Cluster_Label'] = kmeans.labels_

# Mapping nama cluster agar lebih intuitif (berdasarkan rata-rata Total Pembayaran)
cluster_means = df.groupby('Cluster_Label')['Total Pembayaran'].mean().sort_values()
cluster_mapping = {
    cluster_means.index[0]: 'Low Value',
    cluster_means.index[1]: 'Mid-Low Value',
    cluster_means.index[2]: 'Mid-High Value',
    cluster_means.index[3]: 'High Value (VIP)'
}
df['Segmentasi_Pelanggan'] = df['Cluster_Label'].map(cluster_mapping)

# Menyimpan hasil clustering
clustered_file = os.path.join(output_dir, 'clustered_data.csv')
df.to_csv(clustered_file, index=False, sep=';')
print(f"Hasil clustering disimpan di: {clustered_file}")


# ==========================================
# 2. MARKET BASKET ANALYSIS (Association Rules)
# ==========================================
print("\n[2] Menjalankan Market Basket Analysis (Apriori)...")

# Mempersiapkan data transaksi (1 baris = 1 list barang)
# Filter hanya transaksi yang memiliki lebih dari 1 item (opsional, tapi memisahkan koma)
# Atau biarkan semua dan split by comma
transactions = df['product_categories'].dropna().apply(lambda x: [item.strip() for item in x.split(',')]).tolist()

# Transformasi menjadi matrix One-Hot Encoding
te = TransactionEncoder()
te_ary = te.fit(transactions).transform(transactions)
df_apriori = pd.DataFrame(te_ary, columns=te.columns_)

# Mencari frequent itemsets (min_support = 0.1% agar dapat menangkap pola umum)
frequent_itemsets = apriori(df_apriori, min_support=0.001, use_colnames=True)

if not frequent_itemsets.empty:
    # Membuat association rules (confidence >= 1%)
    rules = association_rules(frequent_itemsets, metric="confidence", min_threshold=0.01)
    
    if not rules.empty:
        # Sort berdasarkan lift
        rules = rules.sort_values('lift', ascending=False)
        
        # Format ke bentuk string agar mudah disimpan ke JSON / CSV
        rules['antecedents'] = rules['antecedents'].apply(lambda x: list(x))
        rules['consequents'] = rules['consequents'].apply(lambda x: list(x))
        
        rules_csv = os.path.join(output_dir, 'association_rules.csv')
        rules.to_csv(rules_csv, index=False)
        
        # Konversi ke dictionary untuk JSON
        rules_json = rules.to_dict(orient='records')
        rules_json_file = os.path.join(output_dir, 'association_rules.json')
        with open(rules_json_file, 'w') as f:
            json.dump(rules_json, f, indent=4)
            
        print(f"Ditemukan {len(rules)} aturan asosiasi.")
        print(f"Aturan asosiasi disimpan di: {rules_json_file} dan {rules_csv}")
    else:
        print("Tidak ada rules yang memenuhi threshold confidence 0.1")
else:
    print("Tidak ada frequent itemsets yang memenuhi threshold min_support 0.01")

print("\nSelesai! Kedua algoritma telah diimplementasikan.")
