# DESIGN SYSTEM & UI/UX SPECIFICATION
## SISTEM INFORMASI GEOGRAFIS BERBASIS WEB UNTUK PEMETAAN DAN KLASTERISASI USAHA PARIWISATA KOTA PALEMBANG MENGGUNAKAN ALGORITMA DBSCAN

* **Dokumen:** Standar Desain Visual, Komponen Antarmuka, dan Design Tokens  
* **Program Studi:** Sarjana Terapan (D4) Manajemen Informatika, Politeknik Negeri Sriwijaya  
* **Acuan Desain:** *Clean Enterprise UI* & Portal Resmi Pemerintah (*Gastrodiplomasi Kemlu RI Standard*)  
* **Identitas Warna Utama:** **Ampera Maroon** (Merah Hati Khas Jembatan Ampera Palembang)  
* **Status Dokumen:** Baseline Resmi & Terkunci  
* **Terakhir Diperbarui:** 23 Oktober 2026  

---

## DAFTAR ISI
1. [Filosofi Desain & Anti-AI Slop Manifesto](#1-filosofi-desain--anti-ai-slop-manifesto)
2. [Design Tokens & Palet Warna Resmi](#2-design-tokens--palet-warna-resmi)
3. [Tipografi & Skala Hirarki Huruf](#3-tipografi--skala-hirarki-huruf)
4. [Sistem Ikonografi & Aset Vektor Peta](#4-sistem-ikonografi--aset-vektor-peta)
5. [Spesifikasi Komponen Antarmuka (UI Components)](#5-spesifikasi-komponen-antarmuka-ui-components)
6. [Tata Letak Halaman (Layout Wireframe Templates)](#6-tata-letak-halaman-layout-wireframe-templates)
7. [Aturan Tampilan Responsif (Mobile-Friendly Breakpoints)](#7-aturan-tampilan-responsif-mobile-friendly-breakpoints)

---

## 1. FILOSOFI DESAIN & ANTI-AI SLOP MANIFESTO

Sistem ini dibangun untuk instansi pemerintah (Dinas Pariwisata Kota Palembang) dan pengujian sidang akademik. Seluruh antarmuka **WAJIB** mencerminkan presisi teknis insinyur perangkat lunak profesional (*Software Engineer*), bukan karya generik kecerdasan buatan (*AI Slop*).

### Aturan Mutlak Larangan Desain (Design Code of Conduct):
1. **ZERO EMOJI POLICY (Haram Emoji):**
   * Dilarang keras menggunakan karakter emoji Unicode (seperti 📍, 🏨, 📊, ✅, ❌, ⚠️) di seluruh halaman web, tombol, tabel, kartu, pesan notifikasi, maupun *popup* peta. Seluruh indikator grafis wajib menggunakan **Vektor SVG Resmi**.
2. **NO GRADIENT TEXT & MESH GLOW:**
   * Dilarang menggunakan teks pelangi/gradasi (`bg-clip-text text-transparent`) dan efek latar belakang bola bercahaya (*blur radial mesh*). Teks wajib berwarna solid dengan kontras tinggi.
3. **NO GLOWING / NEON SHADOWS:**
   * Dilarang menggunakan bayangan berlebihan (`shadow-2xl`, bayangan berwarna, atau efek lampu neon). Sistem menggunakan elevasi minimal (`shadow-sm`) dan pemisah garis tepi 1 piksel yang tegas (*crisp 1px borders*).
4. **NO EXTREME ROUNDED CORNERS:**
   * Dilarang menggunakan sudut membulat berlebihan (`rounded-3xl` atau `rounded-full` pada kartu kotak). Standar radius sudut adalah **8px (`rounded-lg`)** atau **6px (`rounded-md`)**.
5. **HIGH CONTRAST (WCAG AA COMPLIANT):**
   * Semua teks informasi harus terbaca jelas dengan kontras minimal 4.5:1 terhadap warna latarnya. Tidak boleh ada teks abu-abu terang di atas latar putih.

---

## 2. DESIGN TOKENS & PALET WARNA RESMI

Sistem menggunakan identitas visual berbasis **Merah Ampera (*Ampera Maroon*)** yang dipadukan dengan palet abu-abu netral (*Slate*) untuk menciptakan kesan berwibawa, modern, dan bersih.

```text
+-----------------------------------------------------------------------------------------+
| PALET WARNA RESMI (AMPERA MAROON DESIGN TOKENS)                                         |
+-----------------------------------------------------------------------------------------+
| Nama Token         | Hex Code | Kelas Tailwind CSS   | Penggunaan Utama                 |
|--------------------+----------+----------------------+----------------------------------|
| Ampera Maroon Base | #8B1E2D  | bg-[#8B1E2D]         | Brand utama, navbar, tombol aksi |
| Ampera Maroon Dark | #701422  | bg-[#701422]         | State hover tombol primer        |
| Ampera Maroon Light| #FDF2F4  | bg-[#FDF2F4]         | Background badge & tab aktif     |
| Ampera Maroon Line | #F3BAC2  | border-[#F3BAC2]     | Border elemen highlight maroon   |
+-----------------------------------------------------------------------------------------+
| PALET NETRAL & TEKS (HIGH-READABILITY SLATES)                                           |
+-----------------------------------------------------------------------------------------+
| Dark Ink Text      | #0F172A  | text-slate-900       | Judul, angka metrik, teks utama  |
| Body Text          | #334155  | text-slate-700       | Paragraf, label formulir         |
| Muted Text         | #64748B  | text-slate-500       | Keterangan sekunder, sub-judul   |
| Crisp Border Line  | #E2E8F0  | border-slate-200     | Garis pemisah kartu & tabel 1px  |
| Surface Card       | #FFFFFF  | bg-white             | Warna latar kartu & modal form   |
| App Background     | #F8FAFC  | bg-slate-50          | Warna latar belakang halaman web |
+-----------------------------------------------------------------------------------------+
```

---

### Palet Warna Spasial Klaster DBSCAN (*Subdued Categorical Palette*)
Peta Leaflet dilarang menggunakan warna pelangi yang menyilaukan. Warna klaster diatur menggunakan warna solid kategorikal yang tidak bertabrakan dengan Ampera Maroon:

| Entitas Spasial | Hex Code | Visual di Peta Leaflet | Keterangan |
| :--- | :---: | :--- | :--- |
| **Klaster 1 (Sentra Utama)** | `#1D4ED8` | Pin Marker Biru & Poligon Biru Transparan (`fillOpacity: 0.2`) | Area kepadatan tertinggi (pusat kota) |
| **Klaster 2 (Sentra Kedua)** | `#047857` | Pin Marker Hijau Zamrud & Poligon Hijau Transparan | Area koridor komersial sekunder |
| **Klaster 3 (Sentra Ketiga)**| `#B45309` | Pin Marker Cokelat Perunggu & Poligon Amber Transparan | Sentra pariwisata pendukung |
| **Klaster 4 (Sentra Keempat)**| `#4338CA` | Pin Marker Indigo & Poligon Indigo Transparan | Zona aglomerasi tambahan |
| **Klaster 5+ (Klaster Lain)**| `#0F766E` | Pin Marker Teal & Poligon Teal Transparan | Klaster minor |
| **Titik Noise (Terpencil)**  | `#64748B` | Lingkaran Titik Abu-Abu Kecil (Tanpa Poligon) | Usaha yang terisolasi di pinggiran |

---

### Palet Status Verifikasi Data (*Dual-State Badges*)
* **Status Verified (1.421 Data):**
  * Teks: `#047857` (`text-emerald-700`)
  * Latar: `#ECFDF5` (`bg-emerald-50`)
  * Garis Tepi: `#A7F3D0` (`border-emerald-200`)
* **Status Unverified (1.388 Data):**
  * Teks: `#475569` (`text-slate-600`)
  * Latar: `#F1F5F9` (`bg-slate-100`)
  * Garis Tepi: `#CBD5E1` (`border-slate-300`)

---

### Palet Status Operasional Faktual (*Tri-State Badges*)
* **Status Buka / Aktif (1.281 Data):**
  * Teks: `#047857` (`text-emerald-700`)
  * Latar: `#ECFDF5` (`bg-emerald-50`)
  * Garis Tepi: `#A7F3D0` (`border-emerald-200`)
  * Indikator Peta: Pin Marker Berwarna Kategori Solid
* **Status Tutup Permanen / Bangkrut (109 Data):**
  * Teks: `#BE123C` (`text-rose-700`)
  * Latar: `#FFF1F2` (`bg-rose-50`)
  * Garis Tepi: `#FECDD3` (`border-rose-200`)
  * Indikator Peta: Pin Marker Abu-abu Kemerahan dengan Ikon Peringatan
* **Status Tutup Sementara (31 Data):**
  * Teks: `#B45309` (`text-amber-700`)
  * Latar: `#FFFBEB` (`bg-amber-50`)
  * Garis Tepi: `#FDE68A` (`border-amber-200`)
  * Indikator Peta: Pin Marker Kuning Amber

---

## 3. TIPOGRAFI & SKALA HIRARKI HURUF

Sistem menggunakan font Google **Plus Jakarta Sans** untuk seluruh teks antarmuka dan **Monospace** untuk angka koordinat geospasial.

### Konfigurasi Font di Tailwind CSS (`tailwind.config.js`):
```javascript
module.exports = {
  theme: {
    extend: {
      fontFamily: {
        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
        mono: ['"JetBrains Mono"', 'Menlo', 'monospace'],
      },
    },
  },
}
```

### Skala Tipografi:
1. **Display H1 (Judul Utama Hero):** `text-2xl md:text-3xl font-bold tracking-tight text-slate-900`
2. **Section Title H2:** `text-lg md:text-xl font-bold text-slate-900`
3. **Card Title H3 / Modal Title:** `text-base font-semibold text-slate-900`
4. **Angka Metrik (Counter Besar):** `text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight`
5. **Body Text Standar:** `text-sm font-normal text-slate-700 leading-relaxed`
6. **Micro Label & Badge:** `text-xs font-semibold uppercase tracking-wider`
7. **Koordinat Spasial (Lat/Long):** `font-mono text-xs text-slate-600 font-medium` (contoh: `-2.98359600, 104.75414490`)

---

## 4. SISTEM IKONOGRAFI & ASET VEKTOR PETA

### 4.1 Standar Ikon Vektor Antarmuka (Heroicons v2)
Seluruh ikon tombol, menu, navigasi, dan indikator status **wajib menggunakan Heroicons Outline (24x24 px, stroke-width: 1.5)**.

* **Ikon Tambah Usaha:** `heroicon-o-plus`
* **Ikon Peta & Marker:** `heroicon-o-map-pin`
* **Ikon Dashboard / Analisis:** `heroicon-o-chart-bar`
* **Ikon Pencarian:** `heroicon-o-magnifying-glass`
* **Ikon Unduh PDF:** `heroicon-o-arrow-down-tray`
* **Ikon Filter:** `heroicon-o-funnel`
* **Ikon Ceklis Status:** `heroicon-o-check-circle`

Contoh implementasi tombol aksi di Blade:
```html
<button class="inline-flex items-center gap-2 px-3.5 py-2 bg-[#8B1E2D] hover:bg-[#701422] text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-150">
    <svg class="w-4 h-4 stroke-[1.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
    </svg>
    <span>Tambah Usaha Baru</span>
</button>
```

---

### 4.2 Marker Pin Vektor di Peta Leaflet (Custom SVG `divIcon`)
Dilarang menggunakan file `.png` buram bawaan Leaflet. Sistem menggunakan pin vektor SVG dengan bentuk tetesan ramping geometris:

* **Dimensi Pin:** Lebar $28\text{ px}$, Tinggi $38\text{ px}$.
* **Struktur SVG Pin:**
  * Badan pin: Bentuk *teardrop* dengan warna sesuai kategori.
  * Lingkaran tengah: Latar putih diameter 12px berisi ikon vektor kategori (garpu-sendok untuk Resto, gedung untuk Hotel, koper untuk Travel).
  * Bayangan titik tanah: Bayangan elips kecil tipis di bawah ujung pin (`rgba(0,0,0,0.15)`).

---

### 4.3 Peta Dasar (*Basemap*) Minimalis
* **Penyedia Peta:** **CartoDB Positron (Light Mode)** via Leaflet TileLayer:
  * URL: `https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png`
* **Karakteristik Visual:** Warna jalan dan gedung berwarna abu-abu sangat muda dan bersih. Hal ini membuat marker pin dan poligon klaster DBSCAN terlihat sangat tajam tanpa polusi visual dari label jalanan yang terlalu ramai.

---

## 5. SPESIFIKASI KOMPONEN ANTARMUKA (UI COMPONENTS)

### 5.1 Kartu Statistik Dashboard (*Metric Card*)
* **Kelas Tailwind:** `bg-white border border-slate-200 rounded-lg p-5 shadow-sm`
* **Tata Letak:**
  * Baris 1: Label kategori (huruf kecil abu-abu `text-xs font-medium text-slate-500 uppercase tracking-wider`) berdampingan dengan kotak ikon kecil berlatar halus (`w-8 h-8 rounded-md bg-slate-100 flex items-center justify-center text-slate-600`).
  * Baris 2: Angka counter besar (`text-2xl font-bold text-slate-900 mt-2`).
  * Baris 3: Keterangan tambahan singkat (`text-xs text-slate-500 mt-1`).

---

### 5.2 Kontrol Mengambang Peta (*Floating Action Pills*)
Mengadopsi gaya modern portal Gastrodiplomasi Kemlu RI, tombol filter dan dropdown diletakkan mengambang di pojok atas peta dengan efek kaca bersih:
* **Kelas Tailwind:** `bg-white/95 backdrop-blur-sm border border-slate-200/80 rounded-lg p-2 shadow-sm flex items-center gap-2`
* **Isi Kontrol:**
  * Dropdown Pemilihan Mode: `[ Mode Peta Sebaran v ]`
  * Dropdown Filter Sektor: `[ Semua Kategori v ]`
  * Input Pencarian Cepat: Input teks minimalis dengan ikon kaca pembesar.

---

### 5.3 Kartu Informasi Popup Marker (*Leaflet Popup Card*)
Saat marker pin di peta diklik oleh pengguna:
* **Lebar Maksimal:** `max-w-xs (290px)`
* **Gaya Popup Leaflet:** Hapus border default biru Leaflet; ganti dengan container putih bersih ber-border `border-slate-200 rounded-lg shadow-md p-3.5`.
* **Hierarki Informasi di Dalam Popup:**
  1. **Baris Atas (Dua Badge):** Badge Kategori KBLI (abu-abu Slate) berdampingan dengan **Badge Status Operasional** (Hijau untuk `Buka`, Merah untuk `Tutup Permanen`, Amber untuk `Tutup Sementara`).
  2. **Nama Brand Proyek Usaha:** (`font-bold text-slate-900 text-sm leading-snug mt-1`).
  3. **Nama Badan Hukum PT/CV:** (`text-xs text-slate-500 font-medium`).
  4. **Alamat Lengkap & Kelurahan:** (`text-xs text-slate-600 mt-1.5 leading-relaxed`).
  5. **Baris Bawah (Identitas OSS & Geospasial):** NIB OSS & Koordinat Presisi (`font-mono text-[11px] text-slate-500 bg-slate-50 p-1.5 rounded mt-2 border border-slate-100 flex justify-between`).

---

### 5.4 Formulir Input & Map Picker Terbelah (*Split-Screen Modal Form*)
Modal form tambah/edit data terbagi menjadi dua kolom proporsional pada layar desktop:
* **Kolom Kiri (Lebar 50%):** Formulir teks vertikal dengan input berstandar Tailwind:
  * Input Field: `w-full rounded-lg border-slate-300 text-sm text-slate-900 focus:border-[#8B1E2D] focus:ring-1 focus:ring-[#8B1E2D] py-2 px-3`
* **Kolom Kanan (Lebar 50%):** Kontainer peta Leaflet interaktif setinggi 400px dengan border `border-slate-200 rounded-lg overflow-hidden`:
  * Di atas peta ada kotak pencarian cepat alamat.
  * Pin marker merah di tengah peta dapat digeser bebas (*draggable: true*).
  * Di bawah peta terdapat dua kotak koordinat *read-only* berlatar abu-abu tipis: `Latitude [-2.983596]` dan `Longitude [104.754145]`.

---

## 6. TATA LETAK HALAMAN (LAYOUT WIREFRAME TEMPLATES)

### 6.1 Layout Navbar Utama (Desktop)
```text
+-------------------------------------------------------------------------------------------------------------+
| [LOGO PALEMBANG] SIG PARIWISATA PALEMBANG  |  Beranda   Peta Sebaran   Pusat Usaha Wisata     [Masuk Admin] |
+-------------------------------------------------------------------------------------------------------------+
```
* **Kelas Tailwind Navbar:** `sticky top-0 z-50 bg-white border-b border-slate-200 h-16 flex items-center justify-between px-6 shadow-sm`
* **Elemen Kiri:** Logo Pemkot Palembang (`h-9 w-auto`), garis pembatas vertikal 1px tipis, dan judul sistem tebal (`text-sm font-bold text-slate-900`).
* **Elemen Tengah:** Navigasi teks ramah pengguna dengan penanda tautan aktif garis bawah Ampera Maroon (`border-b-2 border-[#8B1E2D] text-[#8B1E2D] font-semibold`). Menghilangkan istilah kaku teknis agar mudah dipahami masyarakat umum dan dewan penguji.
* **Elemen Kanan:** Tombol Masuk Administrator Dinas.

---

### 6.2 Layout Halaman 1: Beranda & Statistik Pariwisata (`/`)
```text
+-------------------------------------------------------------------------------------------------------------+
| HERO SECTION: Selamat Datang di Portal SIG Usaha Pariwisata Kota Palembang (Ampera Maroon Banner)           |
| Subtitle: Pemetaan Geospasial & Pemantauan Sebaran Ekonomi Pariwisata Daerah Berbasis Data Resmi OSS-RBA   |
| [ Jelajahi Peta Sebaran ]   [ Lihat Pusat Usaha Wisata ]                                                    |
+-------------------------------------------------------------------------------------------------------------+
| 4 KARTU METRIK EKSEKUTIF:                                                                                   |
| [ Total Izin OSS: 2.809 ]  [ Titik Terpetakan: 1.421 ]  [ Usaha Aktif: 1.281 ]  [ Gulung Tikar: 109 ]       |
+-------------------------------------------------------------------------------------------------------------+
| DIAGRAM ANALITIK:                                                                                           |
| +-------------------------------------------------------+ +-----------------------------------------------+ |
| | DIAGRAM BATANG: Sebaran Kepadatan Usaha di 18 Wilayah | | DIAGRAM DONAT: Proporsi Sektor Usaha Pariwisata| |
| | (Ilir Barat I, Ilir Timur I, Kemuning, Sukarami...)   | | (Kuliner 70%, Kafe 21%, Biro Travel, Hotel...) | |
| +-------------------------------------------------------+ +-----------------------------------------------+ |
+-------------------------------------------------------------------------------------------------------------+
```

---

### 6.3 Layout Halaman 2: Peta Sebaran Usaha (`/peta`)
```text
+-------------------------------------------------------------------------------------------------------------+
| NAVBAR UTAMA                                                                                                |
+-------------------------------------------------------------------------------------------------------------+
| PETA LEAFLET LAYAR PENUH DENGAN KONTROL MENGAMBANG:                                                         |
|                                                                                                             |
|    +------------------------------------+---------------------------------------+                          |
|    | [•] TAB 1: SEBARAN TEMPAT USAHA    | [ ] TAB 2: KEPADATAN PER KECAMATAN    | <-- Tab Switcher         |
|    +------------------------------------+---------------------------------------+                          |
|                                                                                                             |
|  +-------------------------------------------------------------------------------------------------------+  |
|  | FILTER: Status: [ Usaha Buka v ]  Kategori: [ Semua Sektor v ]   Cari: [ Ketik nama tempat... ][ CARI]|  |
|  +-------------------------------------------------------------------------------------------------------+  |
|                                                                                                             |
|                                 +-------------------------------------+                                     |
|                                 | [KULINER]  [STATUS: BUKA]           | <-- Popup Card saat marker diklik   |
|                                 | RM Sederhana Sudirman               |                                     |
|                                 | PT. Sederhana Musi Jaya             |                                     |
|                                 | Jl. Jend. Sudirman No. 128          |                                     |
|                                 | NIB: 9120205132935 | -2.983, 104.75 |                                     |
|                                 +------------------v------------------+                                     |
|                                                   * (Pin Marker Solid)                                      |
+-------------------------------------------------------------------------------------------------------------+
```

---

### 6.4 Layout Halaman 3: Pusat Usaha Wisata (`/zonasi`)
```text
+-------------------------------------------------------------------------------------------------------------+
| NAVBAR UTAMA                                                                                                |
+-------------------------------------------------------------------------------------------------------------+
| PETA LEAFLET LAYAR PENUH (FULL BLEED) DENGAN FLOATING WORKBENCH:                                            |
|                                                                                                             |
|  +---------------------------------------------------+                                                      |
|  | PENGATURAN PUSAT USAHA WISATA             [_] [X]  | <-- Floating Glassmorphism Workbench                 |
|  +---------------------------------------------------+     (Bisa diminimalkan / dilipat)                    |
|  | Cakupan Basis Data:                               |                                                      |
|  |   (•) Hanya Usaha Aktif / Buka (1.281) [Default]  |               (AREA BATAS PUSAT USAHA 1)             |
|  |   ( ) Seluruh Usaha Termasuk Tutup (1.421)        |                     *  *  *  *                       |
|  |                                                   |                    * TITIK INTI *                    |
|  | Bidang Usaha: [ Semua Sektor Usaha       v ]      |                     *  *  *  *                       |
|  |                                                   |                                                      |
|  | Jarak Maksimal Antar Tempat:                      |                                                      |
|  | [============O===================] 300 Meter     |                                (AREA BATAS PUSAT 2)  |
|  | (Radius pencarian / Epsilon)                      |                                    *   *   *         |
|  |                                                   |                                   * PUSAT *          |
|  | Minimal Tempat Berkumpul:                         |                                    *   *   *         |
|  | [=======O========================] 4 Usaha        |          o (Usaha Tersebar/Noise)                    |
|  | (Batas minimal pembentukan / MinPts)              |                                                      |
|  |                                                   |                                                      |
|  | [ TEMUKAN PUSAT USAHA WISATA ] (Tombol Maroon)    |                                                      |
|  +---------------------------------------------------+                                                      |
|                                                                                                             |
|                                                                                                             |
|                +-----------------------------------------------------------------------+                    |
|                | HASIL PENEMUAN PUSAT USAHA (FLOATING BOTTOM DOCK):                    |                    |
|                | Ditemukan: 42 Pusat Usaha | Di Luar Pusat: 202 Usaha | Waktu: 812 ms  |                    |
|                | Kerapihan Kawasan (Silhouette Score): 0.58 [ Terpisah Sangat Rapi ]   |                    |
|                +-----------------------------------------------------------------------+                    |
+-------------------------------------------------------------------------------------------------------------+
```

---

### 6.5 Layout Halaman 4: Dashboard Eksekutif & Statistik Mutu Data (`/admin`)
```text
+-------------------------------------------------------------------------------------------------------------+
| NAVBAR ADMIN: [LOGO] SIG PARIWISATA | Beranda  Peta Sebaran  Pusat Usaha Wisata | [Dashboard] [Data Usaha]  |
+-------------------------------------------------------------------------------------------------------------+
| HEADER: Dashboard Eksekutif Pemantauan Usaha Pariwisata Kota Palembang                                      |
| Subtitle: Laporan Kinerja Perizinan, Kesiapan Geospasial, & Analisis Sektoral     [ Unduh Laporan PDF ]     |
+-------------------------------------------------------------------------------------------------------------+
| 4 KARTU METRIK MUTU DATA:                                                                                   |
| [ Total Izin OSS: 2.809 ]  [ Terpetakan: 1.421 (50,6%) ]  [ Antrean: 1.388 (49,4%) ]  [ Aktif: 1.281 ]     |
+-------------------------------------------------------------------------------------------------------------+
| BARIS 1: 2 GRAFIK ANALITIS                                                                                  |
| +-----------------------------------------------------+ +-------------------------------------------------+ |
| | GRAFIK 1: Kesiapan Data Geospasial (Donut Chart)    | | GRAFIK 2: Ketahanan Usaha per Sektor (Bar Chart)| |
| | (50,6% Terverifikasi Spasial vs 49,4% Antrean)      | | (Buka vs Tutup Permanen di Resto, Kafe, Hotel..)| |
| +-----------------------------------------------------+ +-------------------------------------------------+ |
+-------------------------------------------------------------------------------------------------------------+
| BARIS 2: TREN PERTUMBUHAN IZIN TAHUNAN                                                                      |
| +---------------------------------------------------------------------------------------------------------+ |
| | GRAFIK 3: Tren Pendaftaran Izin Usaha Baru per Tahun (Line Chart: 2019 - 2024 dari Tanggal Pengajuan OSS)| |
| +---------------------------------------------------------------------------------------------------------+ |
+-------------------------------------------------------------------------------------------------------------+
```

---

### 6.6 Layout Halaman 5: Meja Kerja Kelola Data Usaha (`/admin/usaha`)
```text
+-------------------------------------------------------------------------------------------------------------+
| NAVBAR ADMIN                                                                                                |
+-------------------------------------------------------------------------------------------------------------+
| HEADER: Meja Kerja Pemeliharaan Data Usaha Pariwisata                                                       |
|                                                                                                             |
| TOMBOL AKSI CEPAT (POJOK KANAN ATAS):                                                                       |
| [ + Tambah Usaha Baru ] (Maroon)     [ Impor Berkas Excel DPMPTSP ]     [ Cetak Rekapitulasi PDF ]          |
+-------------------------------------------------------------------------------------------------------------+
| SMART TABS FILTER DATA:                                                                                     |
| [ Semua Data (2.809) ]  [ Terverifikasi (1.421) ]  [ (!) Antrean Verifikasi (1.388) ]  [ Duplikat NIB ]     |
+-------------------------------------------------------------------------------------------------------------+
| FILTER & PENCARIAN:                                                                                         |
| Cari Nama / NIB: [.....................]  Kecamatan: [ Semua v ]  KBLI: [ Semua v ]  [ FILTER ]  [ RESET ]  |
+-------------------------------------------------------------------------------------------------------------+
| TABEL DATA USAHA LENGKAP (DATATABLES):                                                                      |
| No | NIB / Nama Usaha        | Kategori Usaha | Kecamatan  | Koordinat Lat/Long     | Status   | Aksi     |
|----+-------------------------+----------------+------------+------------------------+----------+----------|
| 1  | 9120205132935           | Restoran       | IB I       | -2.983596, 104.754145  | [Buka]   | [Ubah]   |
|    | RM Sederhana Sudirman   | (56101)        |            | Terverifikasi          |          | [Hapus]  |
|----+-------------------------+----------------+------------+------------------------+----------+----------|
| 2  | 0220108741221           | Rumah Minum    | IT I       | Belum Ada Titik        | [Antrean]| [+ Pasang|
|    | Kafe Musi Kopi          | (56303)        |            | (Perlu Verifikasi)     |          |   Titik] |
+-------------------------------------------------------------------------------------------------------------+
| PAGINASI: Menampilkan 1 - 15 dari 2.809 data                                       [< Prev] [1] [2] [Next >]|
+-------------------------------------------------------------------------------------------------------------+
```

---

### 6.7 Wireframe Modal Tambah Usaha Manual + Interactive Map Picker
```text
+-------------------------------------------------------------------------------------------------------------+
| MODAL: TAMBAH DATA USAHA BARU SECARA MANUAL                                                            [X]  |
+-------------------------------------------------------------------------------------------------------------+
| FORMULIR ISIAN MANUAL OSS (KOLOM KIRI 50%)          | INTERACTIVE MAP PICKER (KOLOM KANAN 50%)              |
|                                                     |                                                       |
| Nomor Induk Berusaha (NIB):                         | Cari Alamat / Jalan:                                  |
| [ 9120205132935                                 ]   | [ Jl. Jenderal Sudirman Palembang...          ][CARI] |
|                                                     | +---------------------------------------------------+ |
| Nama Perusahaan (PT / CV / Pemilik):                | | PETA LEAFLET INTERAKTIF (KOTAK 380px)             | |
| [ PT. Sederhana Musi Abadi                      ]   | |                                                   | |
|                                                     | |                 * [PIN MARKER MERAH]              | |
| Nama Komersial / Brand Proyek Usaha:                | |                   (Bisa digeser bebas / Drag)     | |
| [ Restoran Sederhana Sudirman                   ]   | |                   tepat di atap ruko gedung       | |
|                                                     | |                                                   | |
| Kategori Usaha (KBLI):                              | +---------------------------------------------------+ |
| [ 56101 - Restoran                              v ] |                                                       |
|                                                     | Titik Koordinat Hasil Geser Pin (Read-Only/Ketik):    |
| Skala Usaha:          Status Operasional:           | Latitude:              Longitude:                     |
| [ Usaha Kecil   v ]   [ Buka (Aktif)            v ] | [ -2.98359600      ]   [ 104.75414500       ]         |
|                                                     |                                                       |
| Alamat Lengkap & Kelurahan:                         | Petunjuk Teknis:                                      |
| [ Jl. Jend. Sudirman No. 128, Kel. 20 Ilir D. IV]   | Geser pin marker merah di peta tepat ke lokasi gedung |
|                                                     | atau ketikkan angka koordinat manual jika sudah ada.  |
| Kecamatan:            Jumlah Tenaga Kerja (TKI):    |                                                       |
| [ Ilir Timur I  v ]   [ 12 Orang                ]   |                                                       |
+-------------------------------------------------------------------------------------------------------------+
| TOMBOL AKSI MODAL:                                          [ Batal ]   [ Simpan Data Usaha Baru ] (Maroon) |
+-------------------------------------------------------------------------------------------------------------+
```

---

## 7. ATURAN TAMPILAN RESPONSIF (MOBILE-FRIENDLY BREAKPOINTS)

Sistem diatur dengan breakpoints baku Tailwind CSS:
* **Mobile (< 768px):** Layar ponsel pintar.
* **Tablet (768px - 1023px):** Layar tablet / iPad.
* **Desktop ( $\ge 1024$px / Target Utama $1366\times 768$px s/d $1920\times 1080$px):** Layar laptop standar dan PC proyektor sidang.

### Adaptasi pada Tampilan Mobile (Smartphone):
1. **Peta Mendominasi Layar Penuh (*Full-Bleed Map*):**
   * Di ponsel, peta mengisi 100% layar tinggi handphone.
2. **Transformasi Sidebar Menjadi Menu Laci Bawah (*Bottom Sheet Drawer*):**
   * Panel kontrol DBSCAN dan filter kategori di ponsel tidak ditaruh di samping (karena akan mempersempit peta).
   * Panel ditaruh sebagai **Bottom Sheet yang bisa ditarik ke atas (*Swipeable Drawer*)** dengan pegangan garis kecil (*handle bar*) di bagian atasnya.
3. **Kartu Statistik Responsif (1 Kolom):**
   * Di layar HP, 4 kartu metrik berubah dari berjajar horizontal menjadi bertumpuk vertikal 1 kolom (`grid-cols-1 md:grid-cols-4`).
4. **Tabel DataTables dengan Gulir Horizontal Mulus (*Smooth Horizontal Scroll*):**
   * Seluruh tabel perizinan di HP dilengkapi kontainer `overflow-x-auto` sehingga tabel tidak merusak lebar layar ponsel.

---

## KESIMPULAN
Dokumen `DESIGN.md` ini adalah **satu-satunya rujukan visual resmi** kita. Setiap pembuatan file Blade, konfigurasi Tailwind, pembuatan marker peta Leaflet, dan form input wajib merujuk secara patuh pada kode warna, ukuran, dan aturan anti-AI-slop yang telah dikunci di atas.
