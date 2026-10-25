# DOKUMEN PERENCANAAN PENELITIAN & ROADMAP SKRIPSI
**Program Studi:** Sarjana Terapan (D4) Manajemen Informatika  
**Institusi:** Politeknik Negeri Sriwijaya (Polsri)  
**Terakhir Diperbarui:** 23 Oktober 2026  

---

## 1. JUDUL PENELITIAN (TERKUNCI)

### A. Judul Tahap 1: Penelitian Mandiri (Fokus Saat Ini)
> **"Sistem Informasi Geografis Berbasis Web untuk Pemetaan dan Klasterisasi Usaha Pariwisata Kota Palembang Menggunakan Algoritma DBSCAN"**

* **Sistem Informasi Geografis Berbasis Web:** Mengolah data spasial (koordinat Lat/Long), rumus jarak kelengkungan bumi (*Haversine*), dan visualisasi peta interaktif berbasis web (*Leaflet.js*).
* **Pemetaan:** Menampilkan katalog sebaran seluruh titik usaha berizin di Kota Palembang.
* **Klasterisasi:** Menerapkan algoritma *unsupervised learning* (DBSCAN) untuk mengekstrak zona konsentrasi/aglomerasi secara otomatis.
* **Usaha Pariwisata:** Berlandaskan **Pasal 14 UU No. 10 Tahun 2009 tentang Kepariwisataan** (merujuk pada unit bisnis berizin resmi dari OSS Dinas Pariwisata).
* **Kota Palembang:** Wilayah administrasi studi (18 kecamatan).
* **Algoritma DBSCAN:** Mesin komputasi kepadatan spasial (*Density-Based Spatial Clustering of Applications with Noise*).

---

### B. Judul Tahap 2: Roadmap Lanjutan ke Skripsi D4 (Masa Depan)
> **"Sistem Pendukung Keputusan Spasial Berbasis Web untuk Rekomendasi Lokasi Usaha Pariwisata di Kota Palembang Menggunakan Kombinasi Algoritma DBSCAN dan TOPSIS"**

* **Peningkatan Nilai (*Novelty*):** Mengintegrasikan hasil klasterisasi DBSCAN dengan metode Sistem Pendukung Keputusan (SPK) **TOPSIS / AHP** untuk memberikan rekomendasi titik lokasi strategis pembukaan usaha baru bagi calon investor/dinas di area yang belum jenuh.

---

## 2. AUDIT FAKTUAL DATASET

* **Sumber Data:** Data Perizinan Berusaha Berbasis Risiko (OSS) Dinas Pariwisata Kota Palembang.
* **File Rujukan Bersih:** `Final Data Terupdate.xlsx` (Sheet: `Gabungan`).
* **Total Data Mentah:** 2.809 baris.
* **Data Berkoordinat Valid (`verified`):** **1.421 titik (50,6%)**.
  * **`Buka` (Aktif / Fokus Utama Klaster):** **1.281 titik (90,15%)**
  * **`Tutup Permanen` (Gulung Tikar):** **109 titik (7,67%)**
  * **`Tutup Sementara`:** **31 titik (2,18%)**
* **Data Antrean Tanpa Koordinat (`unverified`):** **1.388 baris (49,4%)**.

### Rincian 1.421 Titik Berkoordinat per Sektor KBLI & Status Operasional:
| Kode KBLI | Kategori Usaha | Buka (Aktif) | Tutup Permanen | Tutup Sementara | Total Titik Terverifikasi |
| :---: | :--- | :---: | :---: | :---: | :---: |
| 55110 | Hotel Bintang | 60 | 0 | 0 | 60 |
| 55120 | Hotel Melati | 34 | 1 | 0 | 35 |
| 55191 | Penginapan Remaja (Youth Hostel) | 19 | 0 | 1 | 20 |
| 56101 | Restoran | 627 | 66 | 16 | 709 |
| 56301 | Bar | 22 | 5 | 0 | 27 |
| 56303 | Rumah Minum/Kafe | 269 | 27 | 11 | 307 |
| 79121 | Aktivitas Biro Perjalanan Wisata | 106 | 5 | 2 | 113 |
| 79122 | Aktivitas Biro Perjalanan Ibadah Umroh | 104 | 2 | 1 | 107 |
| 93291 | Klub Malam | 2 | 0 | 0 | 2 |
| 93292 | Karaoke | 21 | 0 | 0 | 21 |
| 93293 | Usaha Arena Permainan | 17 | 3 | 0 | 20 |
| **TOTAL** | **Seluruh Usaha Pariwisata** | **1.281** | **109** | **31** | **1.421** |

---

## 3. LOGIKA KERJA ALGORITMA DBSCAN

### A. Dua Parameter Utama:
1. **$\varepsilon$ (*Epsilon* / Radius Jarak):** Jarak jangkauan fisik antar usaha dalam meter (default pengujian: 500 meter). Dihitung menggunakan formula **Haversine Distance**.
2. **$MinPts$ (*Minimum Points* / Kerapatan):** Ambang batas minimal jumlah usaha bertetangga dalam radius $\varepsilon$ agar sah disebut pusat konsentrasi (default pengujian: 4 atau 5 titik).

### B. Tiga Klasifikasi Titik:
* **Core Point (Titik Inti):** Memiliki tetangga $\ge MinPts$ dalam radius $\le \varepsilon$.
* **Border Point (Titik Batas):** Memiliki tetangga $< MinPts$, tetapi berada di dalam radius $\varepsilon$ dari sebuah *Core Point*.
* **Noise (Pencilan / Usaha Terisolasi):** Usaha di pelosok yang tidak memenuhi syarat *Core* dan tidak menempel pada *Core* mana pun.

### C. Alur Komputasi di Laravel:
1. Query database MySQL berurutan (`ORDER BY id ASC`).
2. Looping perhitungan jarak Haversine antar titik koordinat $(lat_1, lng_1) \leftrightarrow (lat_2, lng_2)$.
3. Pengelompokan titik inti yang saling terhubung menjadi poligon area klaster (*Convex Hull*).
4. Response JSON dikirim ke frontend Leaflet.js untuk diwarnai per klaster.
5. Evaluasi kualitas klaster dihitung menggunakan **Silhouette Coefficient**.

---

## 4. ARSITEKTUR SISTEM (WEBGIS LARAVEL)

* **Backend:** PHP 8+ (Framework Laravel).
* **Database:** MySQL (XAMPP).
* **Frontend:** Blade Template, Tailwind/Bootstrap, Leaflet.js (Peta Open-Source).

### Empat Modul Utama Sistem:
1. **Dashboard Eksekutif:** Statistik total usaha, diagram sebaran per kecamatan, ringkasan jumlah klaster.
2. **Katalog Pemetaan Spasial:** Peta interaktif seluruh titik usaha dengan filter checklist layer kategori dan pencarian nama.
3. **Modul Klasterisasi DBSCAN (Jantung Sistem):** Form input parameter dinamis ($\varepsilon$ dan $MinPts$), tombol proses, visualisasi marker warna-warni, serta poligon batas area konsentrasi.
4. **Manajemen Data Usaha (CRUD):** Fitur input usaha baru dilengkapi peta interaktif dengan *marker drag-and-drop* agar koordinat masa depan selalu presisi (Rp 0 / bebas biaya API).

---

## 5. BENTENG JAWABAN SAAT SIDANG

### Pertanyaan 1: Kenapa memilih DBSCAN, bukan K-Means?
> *"K-Means memaksakan bentuk klaster bulat dan tidak bisa memisahkan noise. Karakteristik kota Palembang memanjang mengikuti koridor jalan dan sungai, sehingga DBSCAN jauh lebih akurat karena mampu menemukan bentuk klaster bebas (arbitrary shape) dan memisahkan usaha terisolasi sebagai noise secara alami."*

### Pertanyaan 2: Kenapa biro travel umroh dan hiburan malam dimasukkan?
> *"Berdasarkan UU No. 10 Tahun 2009 Pasal 14, Jasa Perjalanan Wisata dan Rekreasi Hiburan merupakan bagian sah dari bidang Usaha Pariwisata resmi. Semua data ini tercatat di bawah binaan Kementerian Pariwisata pada sistem perizinan OSS dinas."*

### Pertanyaan 3: Kenapa data dinas ada 2.809 tapi yang dipakai 1.421?
> *"Pada tahap pra-pemrosesan data, 1.388 data dieliminasi karena tidak memiliki alamat yang cukup spesifik untuk diekstraksi koordinatnya. 1.421 data yang digunakan adalah data bersih yang 100% terverifikasi koordinatnya berada di dalam batas Kota Palembang."*

### Pertanyaan 4: Apakah penelitian mandiri ini akan dilanjutkan ke Skripsi D4?
> *"Ya, Bapak/Ibu. Penelitian Mandiri ini adalah fondasi tahap pertama untuk membangun basis data spasial dan mengidentifikasi pola klasterisasi DBSCAN. Untuk Skripsi D4 nanti, sistem ini akan ditingkatkan menjadi **Spatial Decision Support System (SDSS)** dengan mengintegrasikan metode **TOPSIS**, sehingga sistem mampu memberikan rekomendasi manajerial bagi dinas dan investor dalam menentukan titik lokasi strategis pembukaan usaha baru di zona yang belum jenuh."*

---

## 6. LANGKAH TEKNIS SELANJUTNYA (ACTION PLAN)
1. Ajukan judul dan sinopsis formal ke dosen pembimbing.
2. Buat struktur database `db_wisata_palembang` di phpMyAdmin (XAMPP).
3. Import 1.421 data dari `Final Data Terupdate.xlsx` ke tabel `usaha_pariwisata`.
4. Siapkan project Laravel dan integrasikan Leaflet.js dengan basemap Palembang.
