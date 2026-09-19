<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Respondent;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RespondentController extends Controller
{
    public function create()
    {
        return view('respondent.form');
    }

    public function prepare(Request $request)
    {
        $validated = $request->validate([
            'provinsi' => 'required|string',
            'kabupaten' => 'required|string',
            'kecamatan' => 'required|string',
            'desa' => 'required|string',
            'dusun' => 'nullable|string',
            'rt' => 'nullable|string',
            'rw' => 'nullable|string',
            'nama_jalan' => 'nullable|string',
            'nomor_rumah' => 'nullable|string',
            'detail_alamat' => 'required|string',
        ]);

        $queries = [];

        // Query 1: Paling Lengkap
        $q1 = [];
        if (!empty($validated['detail_alamat'])) $q1[] = $validated['detail_alamat'];
        if (!empty($validated['nama_jalan'])) {
            $jalan = $validated['nama_jalan'];
            if (!empty($validated['nomor_rumah'])) {
                $jalan .= ' No. ' . $validated['nomor_rumah'];
            }
            $q1[] = $jalan;
        }
        if (!empty($validated['dusun'])) $q1[] = $validated['dusun'];
        $q1[] = $validated['desa'];
        $q1[] = $validated['kecamatan'];
        $q1[] = $validated['kabupaten'];
        $q1[] = $validated['provinsi'];
        $q1[] = 'Indonesia';
        $queries[] = implode(', ', $q1);

        // Query 2: Tanpa Nomor Rumah (dan tanpa detail_alamat untuk query yang lebih terstruktur ke OSM)
        if (!empty($validated['nama_jalan'])) {
            $q2 = [];
            $q2[] = $validated['nama_jalan'];
            if (!empty($validated['dusun'])) $q2[] = $validated['dusun'];
            $q2[] = $validated['desa'];
            $q2[] = $validated['kecamatan'];
            $q2[] = $validated['kabupaten'];
            $q2[] = $validated['provinsi'];
            $q2[] = 'Indonesia';
            $queries[] = implode(', ', $q2);
        }

        // Query 3: Tanpa Nama Jalan
        $q3 = [];
        if (!empty($validated['dusun'])) $q3[] = $validated['dusun'];
        $q3[] = $validated['desa'];
        $q3[] = $validated['kecamatan'];
        $q3[] = $validated['kabupaten'];
        $q3[] = $validated['provinsi'];
        $q3[] = 'Indonesia';
        $queries[] = implode(', ', $q3);

        // Query 4: Berdasarkan Wilayah
        $q4 = [
            $validated['desa'],
            $validated['kecamatan'],
            $validated['kabupaten'],
            $validated['provinsi'],
            'Indonesia'
        ];
        $queries[] = implode(', ', $q4);

        // Query 5: Tanpa Kecamatan (OSM sering melewatkan kecamatan)
        $q5 = [
            $validated['desa'],
            $validated['kabupaten'],
            $validated['provinsi'],
            'Indonesia'
        ];
        $queries[] = implode(', ', $q5);

        // Query 6: Tanpa Provinsi
        $q6 = [
            $validated['desa'],
            $validated['kabupaten'],
            'Indonesia'
        ];
        $queries[] = implode(', ', $q6);

        // Query 7: Hanya Desa
        $q7 = [
            $validated['desa'],
            'Indonesia'
        ];
        $queries[] = implode(', ', $q7);

        // Hapus query yang sama jika field kosong
        $queries = array_values(array_unique($queries));

        foreach ($queries as $index => $queryString) {
            $attempt = $index + 1;
            Log::info("GEOCODING ATTEMPT $attempt");
            Log::info("query = $queryString");

            try {
                $response = Http::withHeaders([
                    'User-Agent' => 'AplikasiSurveiLokasi/1.0 (admin@survei-lokasi.local)'
                ])
                ->timeout(10)
                ->get('https://nominatim.openstreetmap.org/search', [
                    'format' => 'jsonv2',
                    'addressdetails' => 1,
                    'limit' => 1,
                    'countrycodes' => 'id',
                    'q' => $queryString
                ]);

                Log::info("HTTP STATUS = " . $response->status());

                if ($response->successful()) {
                    $data = $response->json();
                    Log::info("RESULT COUNT = " . count($data));

                    if (is_array($data) && count($data) > 0) {
                        $firstResult = $data[0];
                        Log::info("Reference coordinate: " . $firstResult['lat'] . ", " . $firstResult['lon']);
                        return response()->json([
                            'success' => true,
                            'data' => [
                                'latitude_referensi' => $firstResult['lat'],
                                'longitude_referensi' => $firstResult['lon'],
                                'display_name_referensi' => $firstResult['display_name'] ?? null
                            ]
                        ]);
                    }
                    // Jika count 0, loop akan lanjut ke percobaan berikutnya
                } else {
                    // API Error
                    Log::info("JSON parsing: fail/non-200");
                    return response()->json([
                        'success' => false,
                        'error_type' => 'api_error',
                        'message' => 'Alamat belum dapat diproses. Sistem sedang mengalami kendala saat menyiapkan data alamat. Silakan coba kembali beberapa saat lagi.'
                    ]);
                }
            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                Log::error("Nominatim Timeout/Connection Error: " . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'error_type' => 'timeout',
                    'message' => 'Proses membutuhkan waktu lebih lama. Silakan coba kembali beberapa saat lagi.'
                ]);
            } catch (\Exception $e) {
                Log::error("Nominatim General Exception: " . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'error_type' => 'internal_error',
                    'message' => 'Sistem sedang mengalami kendala internal. Silakan coba kembali beberapa saat lagi.'
                ]);
            }
        }

        // Jika semua fallback gagal dan loop berakhir
        return response()->json([
            'success' => false,
            'error_type' => 'empty',
            'message' => 'Lokasi referensi alamat belum dapat disiapkan. Coba kembali.'
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'nomor_hp' => 'required|string|max:20',
            'provinsi' => 'required|string|max:100',
            'kabupaten' => 'required|string|max:100',
            'kecamatan' => 'required|string|max:100',
            'desa' => 'required|string',
            'dusun' => 'nullable|string',
            'rt' => 'nullable|string',
            'rw' => 'nullable|string',
            'nama_jalan' => 'nullable|string',
            'nomor_rumah' => 'nullable|string',
            'detail_alamat' => 'required|string',
            'latitude_referensi' => 'required|numeric',
            'longitude_referensi' => 'required|numeric',
            'display_name_referensi' => 'nullable|string',
            'latitude_rumah' => 'required|numeric',
            'longitude_rumah' => 'required|numeric',
            'accuracy' => 'required|numeric',
        ]);

        // Tolerance thresholds (Soft limits, tidak lagi memblokir user)
        $SOFT_MAX_ACCURACY = 2000; // 2km
        $SOFT_MAX_DISTANCE = 5000; // 5km dari titik tengah desa (OSM)

        $distance = $this->haversineGreatCircleDistance(
            $validated['latitude_referensi'], 
            $validated['longitude_referensi'], 
            $validated['latitude_rumah'], 
            $validated['longitude_rumah']
        );

        $status = 'VALIDASI_BERHASIL';
        
        // Alih-alih menolak (block), kita hanya menandai statusnya untuk petugas
        if ($validated['accuracy'] > $SOFT_MAX_ACCURACY || $distance > $SOFT_MAX_DISTANCE) {
            $status = 'PERLU_TINJAUAN_MANUAL';
        }

        // Tidak ada lagi error return response()->json success false! User selalu tembus!

        // Save data
        $respondent = Respondent::create([
            'nama' => $validated['nama'],
            'nomor_hp' => $validated['nomor_hp'],
            'provinsi' => $validated['provinsi'],
            'kabupaten' => $validated['kabupaten'],
            'kecamatan' => $validated['kecamatan'],
            'desa' => $validated['desa'],
            'dusun' => $validated['dusun'],
            'rt' => $validated['rt'],
            'rw' => $validated['rw'],
            'detail_alamat' => $validated['detail_alamat'],
            'latitude_referensi' => $validated['latitude_referensi'],
            'longitude_referensi' => $validated['longitude_referensi'],
            'display_name_referensi' => $validated['display_name_referensi'],
            'latitude_rumah' => $validated['latitude_rumah'],
            'longitude_rumah' => $validated['longitude_rumah'],
            'accuracy' => $validated['accuracy'],
            'jarak_dari_referensi' => $distance,
            'location_status' => $status,
            'location_captured_at' => Carbon::now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $respondent
        ]);
    }

    /**
     * Calculates the great-circle distance between two points, with
     * the Haversine formula.
     * @return float Distance in meters
     */
    private function haversineGreatCircleDistance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371000)
    {
        // convert from degrees to radians
        $latFrom = deg2rad($latitudeFrom);
        $lonFrom = deg2rad($longitudeFrom);
        $latTo = deg2rad($latitudeTo);
        $lonTo = deg2rad($longitudeTo);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
        
        return $angle * $earthRadius;
    }
}
