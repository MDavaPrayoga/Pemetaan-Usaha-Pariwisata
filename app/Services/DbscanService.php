<?php

namespace App\Services;

class DbscanService
{
    // Konstanta jari-jari rata-rata bumi dalam satuan kilometer (standar geodesik WGS-84)
    protected const EARTH_RADIUS_KM = 6371.0;

    // Menghitung jarak lengkung bumi antara dua koordinat menggunakan rumus Haversine
    public function haversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        // Mengubah selisih derajat lintang dan bujur menjadi radian
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        // Mengubah derajat lintang kedua titik menjadi radian untuk fungsi trigonometri
        $phi1 = deg2rad($lat1);
        $phi2 = deg2rad($lat2);

        // Menghitung komponen kuadrat separuh tali busur (rumus Haversine bagian a)
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos($phi1) * cos($phi2) *
             sin($dLon / 2) * sin($dLon / 2);

        // Menghitung jarak sudut dalam radian (rumus Haversine bagian c)
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        // Mengalikan jarak sudut dengan jari-jari bumi untuk mendapatkan jarak nyata dalam kilometer
        return self::EARTH_RADIUS_KM * $c;
    }

    // Mencari semua titik tetangga yang berada dalam jangkauan jarak epsilon (eps)
    protected function regionQuery(int $targetIdx, array &$dataset, float $eps): array
    {
        $neighbors = [];
        $targetLat = (float) $dataset[$targetIdx]['latitude'];
        $targetLon = (float) $dataset[$targetIdx]['longitude'];

        // Menghitung jarak dari titik acuan ke setiap titik lain di dalam dataset
        foreach ($dataset as $idx => $point) {
            $dist = $this->haversineDistance(
                $targetLat,
                $targetLon,
                (float) $point['latitude'],
                (float) $point['longitude']
            );

            // Jika jaraknya lebih kecil atau sama dengan eps, masukkan indeks titik ke daftar tetangga
            if ($dist <= $eps) {
                $neighbors[] = $idx;
            }
        }

        return $neighbors;
    }

    // Fungsi utama untuk menjalankan keseluruhan proses klasterisasi DBSCAN
    public function run(array $dataset, float $eps, int $minPts): array
    {
        // Mencatat waktu mulai komputasi untuk mengukur performa algoritma
        $startTime = microtime(true);
        $totalPoints = count($dataset);

        // Jika data kosong, langsung kembalikan struktur hasil kosong
        if ($totalPoints === 0) {
            return [
                'success' => false,
                'message' => 'Dataset kosong.',
                'total_points' => 0,
                'total_clusters' => 0,
                'total_noise' => 0,
                'execution_time_ms' => 0,
                'clusters' => [],
                'noise' => [],
                'points' => [],
            ];
        }

        // Array penanda apakah titik sudah pernah diperiksa (true/false)
        $visited = array_fill(0, $totalPoints, false);

        // Array penyimpan label klaster: 0 = belum diproses, -1 = noise, 1, 2, ... = nomor klaster
        $labels = array_fill(0, $totalPoints, 0);

        // Array penyimpan tipe titik ilmiah DBSCAN: core, border, atau noise
        $pointTypes = array_fill(0, $totalPoints, 'unclassified');

        // Nomor urut klaster dimulai dari nol
        $clusterId = 0;

        // Melakukan iterasi ke setiap titik di dalam dataset
        for ($i = 0; $i < $totalPoints; $i++) {
            // Lewati titik yang sudah pernah dikunjungi pada proses sebelumnya
            if ($visited[$i]) {
                continue;
            }

            // Tandai titik saat ini sebagai sudah dikunjungi
            $visited[$i] = true;

            // Cari semua tetangga yang berada dalam radius epsilon
            $neighbors = $this->regionQuery($i, $dataset, $eps);

            // Jika jumlah tetangga kurang dari batas minimum (MinPts), tandai sementara sebagai noise
            if (count($neighbors) < $minPts) {
                $labels[$i] = -1;
                $pointTypes[$i] = 'noise';
            } else {
                // Jika memenuhi MinPts, buat klaster baru dan jadikan titik ini sebagai Core Point
                $clusterId++;
                $labels[$i] = $clusterId;
                $pointTypes[$i] = 'core';

                // Perluas klaster ke semua tetangga yang terhubung secara kepadatan (density-reachable)
                $this->expandCluster(
                    $i,
                    $neighbors,
                    $clusterId,
                    $eps,
                    $minPts,
                    $dataset,
                    $visited,
                    $labels,
                    $pointTypes
                );
            }
        }

        // Hitung total waktu komputasi dalam satuan milidetik (ms)
        $executionTimeMs = (int) round((microtime(true) - $startTime) * 1000);

        // Menyiapkan wadah untuk mengelompokkan data hasil akhir
        $clusters = [];
        $noise = [];
        $outputPoints = [];

        // Menyusun kembali data titik beserta label dan tipe titiknya
        for ($i = 0; $i < $totalPoints; $i++) {
            $pointData = $dataset[$i];
            $pointData['cluster_label'] = $labels[$i];
            $pointData['tipe_titik'] = $pointTypes[$i];

            $outputPoints[] = $pointData;

            // Pisahkan titik pencilan (noise) dan titik yang masuk anggota klaster
            if ($labels[$i] === -1) {
                $noise[] = $pointData;
            } else {
                $clusters[$labels[$i]][] = $pointData;
            }
        }

        // Mengembalikan seluruh hasil klasterisasi dan metrik evaluasi
        return [
            'success' => true,
            'parameter_eps' => $eps,
            'parameter_minpts' => $minPts,
            'total_points' => $totalPoints,
            'total_clusters' => count($clusters),
            'total_noise' => count($noise),
            'execution_time_ms' => $executionTimeMs,
            'clusters' => $clusters,
            'noise' => $noise,
            'points' => $outputPoints,
        ];
    }

    // Memperluas keanggotaan klaster dari sebuah core point ke seluruh tetangganya
    protected function expandCluster(
        int $rootIdx,
        array $neighbors,
        int $clusterId,
        float $eps,
        int $minPts,
        array &$dataset,
        array &$visited,
        array &$labels,
        array &$pointTypes
    ): void {
        // Antrean pemrosesan titik tetangga
        $queue = $neighbors;

        // Tabel lookup cepat untuk memastikan tidak ada titik yang dimasukkan ganda ke antrean
        $inQueue = array_fill(0, count($dataset), false);
        foreach ($queue as $idx) {
            $inQueue[$idx] = true;
        }

        $qPointer = 0;

        // Telusuri antrean satu per satu sampai habis
        while ($qPointer < count($queue)) {
            $currentIdx = $queue[$qPointer];
            $qPointer++;

            // Jika titik sebelumnya berstatus noise, ubah menjadi border point dari klaster ini
            if ($labels[$currentIdx] === -1) {
                $labels[$currentIdx] = $clusterId;
                $pointTypes[$currentIdx] = 'border';
            }

            // Jika titik belum pernah dikunjungi, tandai dan periksa tetangganya
            if (!$visited[$currentIdx]) {
                $visited[$currentIdx] = true;
                $currentNeighbors = $this->regionQuery($currentIdx, $dataset, $eps);

                // Jika tetangga titik ini juga >= MinPts, titik ini adalah core point tambahan
                if (count($currentNeighbors) >= $minPts) {
                    $pointTypes[$currentIdx] = 'core';

                    // Masukkan tetangga baru ke dalam antrean untuk ditelusuri lebih lanjut
                    foreach ($currentNeighbors as $nbIdx) {
                        if (!$inQueue[$nbIdx]) {
                            $inQueue[$nbIdx] = true;
                            $queue[] = $nbIdx;
                        }
                    }
                } else {
                    // Jika tetangganya < MinPts, titik ini hanya sebagai batas klaster (border point)
                    $pointTypes[$currentIdx] = 'border';
                }
            }

            // Jika titik belum memiliki label klaster, tetapkan ke klaster saat ini
            if ($labels[$currentIdx] === 0) {
                $labels[$currentIdx] = $clusterId;
                if ($pointTypes[$currentIdx] === 'unclassified') {
                    $pointTypes[$currentIdx] = 'border';
                }
            }
        }
    }
}
