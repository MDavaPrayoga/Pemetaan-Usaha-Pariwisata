<?php

namespace App\Services;

class DbscanService
{
    /**
     * Radius rata-rata bumi dalam satuan kilometer (WGS-84 standard).
     */
    protected const EARTH_RADIUS_KM = 6371.0;

    /**
     * Hitung jarak geodetik permukaan bumi antara dua titik koordinat
     * menggunakan rumus Haversine (Great-Circle Distance).
     *
     * @param float $lat1 Lintang titik asal (derajat)
     * @param float $lon1 Bujur titik asal (derajat)
     * @param float $lat2 Lintang titik tujuan (derajat)
     * @param float $lon2 Bujur titik tujuan (derajat)
     * @return float Jarak dalam kilometer
     */
    public function haversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $phi1 = deg2rad($lat1);
        $phi2 = deg2rad($lat2);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos($phi1) * cos($phi2) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return self::EARTH_RADIUS_KM * $c;
    }

    /**
     * Cari semua indeks tetangga yang berada dalam jarak <= epsilon.
     *
     * @param int $targetIdx Indeks titik acuan
     * @param array $dataset Seluruh kumpulan titik koordinat
     * @param float $eps Radius maksimum tetangga (km)
     * @return array Daftar indeks titik tetangga dalam radius eps
     */
    protected function regionQuery(int $targetIdx, array &$dataset, float $eps): array
    {
        $neighbors = [];
        $targetLat = (float) $dataset[$targetIdx]['latitude'];
        $targetLon = (float) $dataset[$targetIdx]['longitude'];

        foreach ($dataset as $idx => $point) {
            $dist = $this->haversineDistance(
                $targetLat,
                $targetLon,
                (float) $point['latitude'],
                (float) $point['longitude']
            );

            if ($dist <= $eps) {
                $neighbors[] = $idx;
            }
        }

        return $neighbors;
    }

    /**
     * Eksekusi algoritma DBSCAN untuk pengelompokan spasial berbasis kepadatan.
     *
     * @param array $dataset Array data usaha yang wajib memiliki kunci: 'id_usaha', 'latitude', 'longitude'
     * @param float $eps Parameter epsilon / radius jangkauan tetangga (kilometer)
     * @param int $minPts Parameter densitas minimum titik untuk membentuk klaster
     * @return array Hasil klasterisasi lengkap dengan metrik performa
     */
    public function run(array $dataset, float $eps, int $minPts): array
    {
        $startTime = microtime(true);
        $totalPoints = count($dataset);

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

        $visited = array_fill(0, $totalPoints, false);
        $labels = array_fill(0, $totalPoints, 0); // 0 = unclassified, -1 = noise, 1..k = cluster_id
        $pointTypes = array_fill(0, $totalPoints, 'unclassified'); // 'core', 'border', 'noise'

        $clusterId = 0;

        for ($i = 0; $i < $totalPoints; $i++) {
            if ($visited[$i]) {
                continue;
            }

            $visited[$i] = true;
            $neighbors = $this->regionQuery($i, $dataset, $eps);

            if (count($neighbors) < $minPts) {
                $labels[$i] = -1;
                $pointTypes[$i] = 'noise';
            } else {
                $clusterId++;
                $labels[$i] = $clusterId;
                $pointTypes[$i] = 'core';

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

        $executionTimeMs = (int) round((microtime(true) - $startTime) * 1000);

        // Rekapitulasi hasil klasterisasi
        $clusters = [];
        $noise = [];
        $outputPoints = [];

        for ($i = 0; $i < $totalPoints; $i++) {
            $pointData = $dataset[$i];
            $pointData['cluster_label'] = $labels[$i];
            $pointData['tipe_titik'] = $pointTypes[$i];

            $outputPoints[] = $pointData;

            if ($labels[$i] === -1) {
                $noise[] = $pointData;
            } else {
                $clusters[$labels[$i]][] = $pointData;
            }
        }

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

    /**
     * Ekspansi klaster secara rekursif berbasis antrean tetangga (density-reachable).
     */
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
        $queue = $neighbors;
        $inQueue = array_fill(0, count($dataset), false);

        foreach ($queue as $idx) {
            $inQueue[$idx] = true;
        }

        $qPointer = 0;

        while ($qPointer < count($queue)) {
            $currentIdx = $queue[$qPointer];
            $qPointer++;

            // Jika sebelumnya ditandai noise, ubah jadi border point di klaster ini
            if ($labels[$currentIdx] === -1) {
                $labels[$currentIdx] = $clusterId;
                $pointTypes[$currentIdx] = 'border';
            }

            if (!$visited[$currentIdx]) {
                $visited[$currentIdx] = true;
                $currentNeighbors = $this->regionQuery($currentIdx, $dataset, $eps);

                if (count($currentNeighbors) >= $minPts) {
                    $pointTypes[$currentIdx] = 'core';
                    // Tambahkan tetangga baru yang belum ada di antrean
                    foreach ($currentNeighbors as $nbIdx) {
                        if (!$inQueue[$nbIdx]) {
                            $inQueue[$nbIdx] = true;
                            $queue[] = $nbIdx;
                        }
                    }
                } else {
                    // Titik terhubung ke core point, tapi jumlah tetangganya < MinPts => Border Point
                    $pointTypes[$currentIdx] = 'border';
                }
            }

            if ($labels[$currentIdx] === 0) {
                $labels[$currentIdx] = $clusterId;
                if ($pointTypes[$currentIdx] === 'unclassified') {
                    $pointTypes[$currentIdx] = 'border';
                }
            }
        }
    }
}
