<?php

namespace App\Services;

class DbscanService
{
    // Konstanta jari-jari rata-rata bumi dalam satuan kilometer (standar geodesik WGS-84)
    public const EARTH_RADIUS_KM = 6371.0;

    /**
     * Menghitung jarak lengkung bumi antara dua koordinat menggunakan rumus Haversine (kilometer).
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
     * Versi cepat Haversine dengan koordinat yang sudah dalam format radian.
     */
    protected function fastHaversine(float $rLat1, float $rLon1, float $rLat2, float $rLon2): float
    {
        $dLat = $rLat2 - $rLat1;
        $dLon = $rLon2 - $rLon1;

        $sinDLat = sin($dLat * 0.5);
        $sinDLon = sin($dLon * 0.5);

        $a = $sinDLat * $sinDLat + cos($rLat1) * cos($rLat2) * $sinDLon * $sinDLon;

        return 12742.0 * atan2(sqrt($a), sqrt(1.0 - $a));
    }

    /**
     * Membentuk poligon pembungkus terluar kawasan sentra menggunakan algoritma Monotone Chain 2D Convex Hull.
     * Mengembalikan koordinat poligon [[lat, lng], [lat, lng], ...]
     */
    public function convexHull(array $points): array
    {
        $n = count($points);
        if ($n < 3) {
            return array_map(fn($p) => [(float) $p['latitude'], (float) $p['longitude']], $points);
        }

        // Urutkan titik secara leksikografis: latitude terkecil ke terbesar, lalu longitude
        usort($points, function ($a, $b) {
            $latA = (float) $a['latitude'];
            $latB = (float) $b['latitude'];
            if ($latA == $latB) {
                return (float) $a['longitude'] <=> (float) $b['longitude'];
            }
            return $latA <=> $latB;
        });

        // Fungsi cross product 2D untuk menguji belokan (vektor OA dan OB)
        $crossProduct = function ($o, $a, $b) {
            $oLon = (float) $o['longitude'];
            $oLat = (float) $o['latitude'];
            $aLon = (float) $a['longitude'];
            $aLat = (float) $a['latitude'];
            $bLon = (float) $b['longitude'];
            $bLat = (float) $b['latitude'];

            return ($aLon - $oLon) * ($bLat - $oLat) - ($aLat - $oLat) * ($bLon - $oLon);
        };

        // Bangun Lower Hull
        $lower = [];
        foreach ($points as $p) {
            while (count($lower) >= 2 && $crossProduct($lower[count($lower) - 2], $lower[count($lower) - 1], $p) <= 0) {
                array_pop($lower);
            }
            $lower[] = $p;
        }

        // Bangun Upper Hull
        $upper = [];
        for ($i = $n - 1; $i >= 0; $i--) {
            $p = $points[$i];
            while (count($upper) >= 2 && $crossProduct($upper[count($upper) - 2], $upper[count($upper) - 1], $p) <= 0) {
                array_pop($upper);
            }
            $upper[] = $p;
        }

        // Hapus elemen terakhir dari masing-masing hull karena duplikat dengan elemen awal
        array_pop($lower);
        array_pop($upper);

        $hull = array_merge($lower, $upper);
        return array_map(fn($p) => [(float) $p['latitude'], (float) $p['longitude']], $hull);
    }

    /**
     * Menghitung nilai validasi klaster Silhouette Coefficient secara matematis.
     * Formula: s(i) = (b(i) - a(i)) / max(a(i), b(i))
     */
    public function calculateSilhouette(array $clusters, array $clusteredPoints): ?float
    {
        $clusterCount = count($clusters);
        $totalPoints = count($clusteredPoints);

        // Silhouette hanya valid jika ada minimal 2 klaster dan minimal 2 titik
        if ($clusterCount < 2 || $totalPoints < 2) {
            return null;
        }

        $totalSilhouette = 0.0;

        for ($i = 0; $i < $totalPoints; $i++) {
            $pi = $clusteredPoints[$i];
            $cI = $pi['cluster_id'];

            $distSame = 0.0;
            $countSame = 0;
            $distOther = []; // cId => ['sum' => float, 'cnt' => int]

            $rLatI = $pi['rad_lat'];
            $rLonI = $pi['rad_lon'];

            for ($j = 0; $j < $totalPoints; $j++) {
                if ($i === $j) continue;
                $pj = $clusteredPoints[$j];

                $dist = $this->fastHaversine($rLatI, $rLonI, $pj['rad_lat'], $pj['rad_lon']);

                if ($pj['cluster_id'] === $cI) {
                    $distSame += $dist;
                    $countSame++;
                } else {
                    $otherC = $pj['cluster_id'];
                    if (!isset($distOther[$otherC])) {
                        $distOther[$otherC] = ['sum' => 0.0, 'cnt' => 0];
                    }
                    $distOther[$otherC]['sum'] += $dist;
                    $distOther[$otherC]['cnt']++;
                }
            }

            // a(i): jarak rata-rata ke titik lain dalam klaster yang sama
            $a_i = $countSame > 0 ? ($distSame / $countSame) : 0.0;

            // b(i): jarak rata-rata terpendek ke titik-titik di klaster tetangga terdekat
            $b_i = INF;
            foreach ($distOther as $oc => $stats) {
                $avgDist = $stats['sum'] / $stats['cnt'];
                if ($avgDist < $b_i) {
                    $b_i = $avgDist;
                }
            }
            if ($b_i === INF) {
                $b_i = 0.0;
            }

            $maxAB = max($a_i, $b_i);
            $s_i = $maxAB > 0 ? (($b_i - $a_i) / $maxAB) : 0.0;

            $totalSilhouette += $s_i;
        }

        return round($totalSilhouette / $totalPoints, 4);
    }

    /**
     * Interpretasi kualitatif skor Silhouette Coefficient agar mudah dipahami publik & penguji sidang.
     */
    public function getSilhouetteLabel(?float $score): string
    {
        if ($score === null) {
            return 'Kurang dari 2 Kawasan (Belum Terbentuk)';
        }

        if ($score >= 0.50) {
            return 'Kawasan Terpisah Sangat Rapi & Kuat';
        } elseif ($score >= 0.25) {
            return 'Kawasan Terpisah Cukup Baik';
        } elseif ($score >= 0.00) {
            return 'Kawasan Cenderung Rapat / Berdekatan';
        } else {
            return 'Kawasan Cenderung Tumpang Tindih';
        }
    }

    /**
     * Mencari titik tetangga dalam radius epsilon.
     */
    protected function regionQueryFast(int $targetIdx, array &$dataset, float $epsKm): array
    {
        $neighbors = [];
        $tLat = $dataset[$targetIdx]['rad_lat'];
        $tLon = $dataset[$targetIdx]['rad_lon'];

        foreach ($dataset as $idx => $point) {
            $dist = $this->fastHaversine($tLat, $tLon, $point['rad_lat'], $point['rad_lon']);
            if ($dist <= $epsKm) {
                $neighbors[] = $idx;
            }
        }

        return $neighbors;
    }

    /**
     * Fungsi utama untuk menjalankan klasterisasi spasial DBSCAN lengkap dengan Convex Hull dan Silhouette Score.
     *
     * @param array $dataset Titik-titik usaha dengan 'latitude' dan 'longitude'
     * @param float $epsKm Jarak radius jangkauan dalam kilometer
     * @param int $minPts Batas minimal titik berkumpul
     * @param float|null $epsMeter Jarak radius jangkauan dalam meter (opsional)
     * @return array
     */
    public function run(array $dataset, float $epsKm, int $minPts, ?float $epsMeter = null): array
    {
        $startTime = microtime(true);
        $totalPoints = count($dataset);

        if ($epsMeter === null) {
            $epsMeter = round($epsKm * 1000.0, 1);
        }

        if ($totalPoints === 0) {
            return [
                'success' => false,
                'message' => 'Dataset kosong.',
                'parameter_eps_meter' => $epsMeter,
                'parameter_eps_km' => $epsKm,
                'parameter_minpts' => $minPts,
                'total_points' => 0,
                'total_clusters' => 0,
                'total_noise' => 0,
                'silhouette_score' => null,
                'silhouette_label' => 'Dataset Kosong',
                'execution_time_ms' => 0,
                'clusters' => [],
                'noise' => [],
                'points' => [],
            ];
        }

        // Pra-konversi seluruh koordinat ke radian untuk mempercepat komputasi Haversine
        for ($i = 0; $i < $totalPoints; $i++) {
            $dataset[$i]['rad_lat'] = deg2rad((float) $dataset[$i]['latitude']);
            $dataset[$i]['rad_lon'] = deg2rad((float) $dataset[$i]['longitude']);
        }

        $visited = array_fill(0, $totalPoints, false);
        $labels = array_fill(0, $totalPoints, 0); // 0 = unvisited, -1 = noise, 1,2.. = cluster
        $pointTypes = array_fill(0, $totalPoints, 'unclassified');
        $clusterId = 0;

        // Iterasi algoritma DBSCAN
        for ($i = 0; $i < $totalPoints; $i++) {
            if ($visited[$i]) {
                continue;
            }

            $visited[$i] = true;
            $neighbors = $this->regionQueryFast($i, $dataset, $epsKm);

            if (count($neighbors) < $minPts) {
                $labels[$i] = -1;
                $pointTypes[$i] = 'noise';
            } else {
                $clusterId++;
                $labels[$i] = $clusterId;
                $pointTypes[$i] = 'core';

                $this->expandClusterFast(
                    $i,
                    $neighbors,
                    $clusterId,
                    $epsKm,
                    $minPts,
                    $dataset,
                    $visited,
                    $labels,
                    $pointTypes
                );
            }
        }

        // Pengelompokan hasil
        $clustersData = [];
        $noisePoints = [];
        $outputPoints = [];
        $clusteredPointsForSilhouette = [];

        for ($i = 0; $i < $totalPoints; $i++) {
            $pointData = $dataset[$i];
            unset($pointData['rad_lat'], $pointData['rad_lon']); // Bersihkan atribut internal radian

            $pointData['cluster_id'] = $labels[$i];
            $pointData['tipe_titik'] = $pointTypes[$i];

            $outputPoints[] = $pointData;

            if ($labels[$i] === -1) {
                $noisePoints[] = $pointData;
            } else {
                $cId = $labels[$i];
                $clustersData[$cId][] = $pointData;

                $clusteredPointsForSilhouette[] = [
                    'cluster_id' => $cId,
                    'rad_lat' => deg2rad((float) $pointData['latitude']),
                    'rad_lon' => deg2rad((float) $pointData['longitude']),
                ];
            }
        }

        // Analisis metadata tiap sentra (Convex Hull, Centroid, Sektor Dominan)
        $clustersResult = [];
        foreach ($clustersData as $cId => $pts) {
            $count = count($pts);
            $sumLat = 0.0;
            $sumLon = 0.0;
            $coreCount = 0;
            $borderCount = 0;
            $sektorCounts = [];

            foreach ($pts as $pt) {
                $sumLat += (float) $pt['latitude'];
                $sumLon += (float) $pt['longitude'];

                if ($pt['tipe_titik'] === 'core') {
                    $coreCount++;
                } else {
                    $borderCount++;
                }

                $judulKbli = $pt['judul_kbli'] ?? 'Lainnya';
                $sektorCounts[$judulKbli] = ($sektorCounts[$judulKbli] ?? 0) + 1;
            }

            arsort($sektorCounts);
            $sektorDominan = key($sektorCounts);

            // Hitung Convex Hull untuk poligon batas sentra
            $hullCoords = $this->convexHull($pts);

            $clustersResult[$cId] = [
                'cluster_id' => $cId,
                'total_titik' => $count,
                'total_core' => $coreCount,
                'total_border' => $borderCount,
                'sektor_dominan' => $sektorDominan,
                'sektor_ringkasan' => array_slice($sektorCounts, 0, 3, true),
                'center' => [
                    'latitude' => round($sumLat / $count, 6),
                    'longitude' => round($sumLon / $count, 6),
                ],
                'convex_hull' => $hullCoords,
                'points' => $pts,
            ];
        }

        // Hitung Silhouette Coefficient untuk pembuktian validitas klaster
        $silhouetteScore = $this->calculateSilhouette($clustersResult, $clusteredPointsForSilhouette);
        $silhouetteLabel = $this->getSilhouetteLabel($silhouetteScore);

        $executionTimeMs = (int) round((microtime(true) - $startTime) * 1000);

        return [
            'success' => true,
            'parameter_eps_meter' => $epsMeter,
            'parameter_eps_km' => $epsKm,
            'parameter_minpts' => $minPts,
            'total_points' => $totalPoints,
            'total_clusters' => count($clustersResult),
            'total_noise' => count($noisePoints),
            'silhouette_score' => $silhouetteScore,
            'silhouette_label' => $silhouetteLabel,
            'execution_time_ms' => $executionTimeMs,
            'clusters' => $clustersResult,
            'noise' => $noisePoints,
            'points' => $outputPoints,
        ];
    }

    /**
     * Memperluas keanggotaan klaster menggunakan antrean BFS yang telah dioptimasi.
     */
    protected function expandClusterFast(
        int $rootIdx,
        array $neighbors,
        int $clusterId,
        float $epsKm,
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

        $ptr = 0;

        while ($ptr < count($queue)) {
            $currIdx = $queue[$ptr++];

            // Jika titik sebelumnya berstatus noise, ubah menjadi border point
            if ($labels[$currIdx] === -1) {
                $labels[$currIdx] = $clusterId;
                $pointTypes[$currIdx] = 'border';
            }

            if (!$visited[$currIdx]) {
                $visited[$currIdx] = true;
                $currNeighbors = $this->regionQueryFast($currIdx, $dataset, $epsKm);

                if (count($currNeighbors) >= $minPts) {
                    $pointTypes[$currIdx] = 'core';
                    foreach ($currNeighbors as $nbIdx) {
                        if (!$inQueue[$nbIdx]) {
                            $inQueue[$nbIdx] = true;
                            $queue[] = $nbIdx;
                        }
                    }
                } else {
                    $pointTypes[$currIdx] = 'border';
                }
            }

            if ($labels[$currIdx] === 0) {
                $labels[$currIdx] = $clusterId;
                if ($pointTypes[$currIdx] === 'unclassified') {
                    $pointTypes[$currIdx] = 'border';
                }
            }
        }
    }
}
