<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use App\Models\Keahlian;
use App\Models\Mahasiswa;
use App\Models\Ruangan;
use App\Models\Sesi;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class MahasiswaController extends Controller
{
    private $orToolsUrl;

    public function __construct()
    {
        $this->orToolsUrl = env('OR_TOOLS_API_URL', 'http://127.0.0.1:8000');
    }

    public function index()
    {
        $allMahasiswa = Mahasiswa::with('keahlian')->get();
        return view('mahasiswa.index', compact('allMahasiswa'));
    }

    public function create()
    {
        $keahlian = Keahlian::all();
        $dosen = [];
        return view('mahasiswa.create', compact('keahlian', 'dosen'));
    }

    public function getDosenByKeahlian($keahlianId)
    {
        $dosen = Dosen::whereHas('keahlians', function ($query) use ($keahlianId) {
            $query->where('keahlians.id', $keahlianId);
        })->get(['id', 'nama', 'nip']);

        return response()->json($dosen);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nama' => 'required|max:100',
            'nim' => 'required|max:45',
            'skripsi' => 'required|max:300',
            'dosen_pembimbing' => 'required|array|min:2|max:2',
            'dosen_pembimbing.*' => 'exists:dosens,id',
            'keahlian' => 'required|exists:keahlians,id',
        ]);

        $mahasiswa = Mahasiswa::create([
            'nama' => $validatedData['nama'],
            'nim' => $validatedData['nim'],
            'skripsi' => $validatedData['skripsi'],
            'keahlian_id' => $validatedData['keahlian'],
        ]);

        $mahasiswa->dosens()->attach($validatedData['dosen_pembimbing']);

        return redirect()->route('mahasiswa.index')->with('success', 'Data berhasil ditambahkan');
    }

    public function show(Mahasiswa $mahasiswa)
    {
        $mahasiswa->load(['keahlian', 'dosens']);
        return view('mahasiswa.show', compact('mahasiswa'));
    }

    public function edit(Mahasiswa $mahasiswa)
    {
        $keahlian = Keahlian::all();
        $dosen = [];
        $selectedKeahlian = $mahasiswa->keahlian_id;
        $selectedDosen = $mahasiswa->dosens->pluck('id')->toArray();
        return view('mahasiswa.edit', compact('mahasiswa', 'keahlian', 'dosen', 'selectedKeahlian', 'selectedDosen'));
    }

    public function update(Request $request, Mahasiswa $mahasiswa)
    {
        $validatedData = $request->validate([
            'nama' => 'required|max:100',
            'nim' => 'required|max:45',
            'skripsi' => 'required|max:300',
            'dosen_pembimbing' => 'required|array|min:2|max:2',
            'dosen_pembimbing.*' => 'exists:dosens,id',
            'keahlian' => 'required|exists:keahlians,id',
        ]);

        $mahasiswa->update([
            'nama' => $validatedData['nama'],
            'nim' => $validatedData['nim'],
            'skripsi' => $validatedData['skripsi'],
            'keahlian_id' => $validatedData['keahlian'],
        ]);

        $mahasiswa->dosens()->sync($validatedData['dosen_pembimbing']);

        return redirect()->route('mahasiswa.index')->with('success', 'Data berhasil diupdate');
    }

    public function destroy(Mahasiswa $mahasiswa)
    {
        $mahasiswa->delete();
        return redirect()->route('mahasiswa.index')->with('success', 'Data berhasil dihapus');
    }

    /**
     * ===================== 
     * INTEGRASI OR-TOOLS 
     * ===================== 
     */

    public function jadwalIndex()
    {
        $mahasiswas = Mahasiswa::with(['keahlian', 'dosens.sesis'])->get();

        // Ambil jadwal dari session
        $jadwalResult = session('jadwal_result', []);

        return view('mahasiswa.jadwal', [
            'mahasiswas' => $mahasiswas,
            'mahasiswaCount' => $mahasiswas->count(),
            'jadwal' => $jadwalResult,
            'jadwalCount' => count($jadwalResult)
        ]);
    }

    public function generateJadwal(Request $request)
    {
        try {
            Log::info('=== START GENERATE JADWAL ===');
            Log::info('Request Input', $request->all());

            // Validasi input
            $validated = $request->validate([
                'max_time_seconds' => 'required|integer|min:30|max:300',
                'allow_partial' => 'nullable|boolean'
            ]);

            // Prepare data
            $mahasiswaData = $this->prepareMahasiswaData();
            $dosenData = $this->prepareDosenData();
            $availabilitasData = $this->prepareAvailabilitasData();
            $ruanganData = $this->prepareRuanganData();
            $hariData = $this->getUniqueHari();
            $waktuData = $this->getUniqueWaktu();

            Log::info('Data Counts', [
                'mahasiswa' => count($mahasiswaData),
                'dosen' => count($dosenData),
                'availabilitas' => count($availabilitasData),
                'ruangan' => count($ruanganData),
                'hari' => count($hariData),
                'waktu' => count($waktuData)
            ]);

            // Validasi data tidak kosong
            if (empty($mahasiswaData)) {
                return back()->with('error', 'Tidak ada mahasiswa dengan 2 pembimbing lengkap');
            }

            if (empty($dosenData)) {
                return back()->with('error', 'Tidak ada data dosen');
            }

            if (empty($ruanganData)) {
                return back()->with('error', 'Tidak ada data ruangan');
            }

            if (empty($hariData) || empty($waktuData)) {
                return back()->with('error', 'Tidak ada data sesi (hari/waktu)');
            }

            $payload = [
                'mahasiswa' => array_values($mahasiswaData),
                'dosen' => array_values($dosenData),
                'availabilitas_dosen' => array_values($availabilitasData),
                'ruangan' => $ruanganData,
                'hari' => $hariData,
                'waktu' => $waktuData,
                'max_time_seconds' => (int) $validated['max_time_seconds'],
                'allow_partial' => $validated['allow_partial'] ?? true
            ];

            Log::info('OR-Tools Payload', [
                'payload_size' => strlen(json_encode($payload)),
                'allow_partial' => $payload['allow_partial']
            ]);

            $apiUrl = $this->orToolsUrl . '/api/penjadwalan-skripsi';
            Log::info('Calling API', ['url' => $apiUrl]);

            // Send request
            try {
                $response = Http::timeout(180)
                    ->withHeaders([
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ])
                    ->post($apiUrl, $payload);

                Log::info('OR-Tools Response', [
                    'status' => $response->status(),
                    'body_length' => strlen($response->body())
                ]);

            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                Log::error('Connection Error', ['message' => $e->getMessage()]);
                return back()->with(
                    'error',
                    'Tidak dapat terhubung ke API OR-Tools. Pastikan server Python berjalan!'
                );
            } catch (\Illuminate\Http\Client\RequestException $e) {
                Log::error('Request Error', ['message' => $e->getMessage()]);
                return back()->with('error', 'Request timeout: ' . $e->getMessage());
            }

            // Handle non-200 status
            if (!$response->successful()) {
                $errorBody = $response->body();
                Log::error('API Error', ['status' => $response->status(), 'body' => $errorBody]);
                return back()->with('error', 'API Error (Status ' . $response->status() . ')');
            }

            // Parse response
            $result = $response->json();

            if (!$result) {
                Log::error('Invalid JSON response');
                return back()->with('error', 'Response API tidak valid');
            }

            Log::debug('API Response Data', $result);

            // Handle success, partial, AND failed status
            $status = $result['status'] ?? 'unknown';

            if ($status === 'success' || $status === 'partial') {
                $jadwal = $result['jadwal'] ?? [];
                $totalMahasiswa = $result['total_mahasiswa'] ?? count($mahasiswaData);
                $terjadwal = $result['mahasiswa_terjadwal'] ?? count($jadwal);

                // 1. Dapatkan nama mahasiswa yang terjadwal
                $terjadwalNames = array_map(function ($j) {
                    return $j['nama'] ?? '';
                }, $jadwal);

                Log::info('Terjadwal Names', $terjadwalNames);

                // 2. Dapatkan nama mahasiswa yang tidak terjadwal dari API
                $tidakTerjadwalNama = $result['mahasiswa_tidak_terjadwal'] ?? [];
                Log::info('Tidak Terjadwal Names from API', $tidakTerjadwalNama);

                // 3. Cross-check: cari mahasiswa yang tidak ada di kedua list
                $allMahasiswaNames = array_map(function ($m) {
                    return $m['nama'] ?? '';
                }, $mahasiswaData);

                Log::info('All Mahasiswa Names', $allMahasiswaNames);

                $missingStudents = array_diff($allMahasiswaNames, $terjadwalNames, $tidakTerjadwalNama);

                if (!empty($missingStudents)) {
                    Log::warning('⚠️ MISSING STUDENTS DETECTED!', [
                        'missing' => array_values($missingStudents),
                        'count' => count($missingStudents)
                    ]);
                }

                // 4. Build detailed unscheduled data
                $tidakTerjadwalData = [];

                $allUnscheduledNames = array_merge($tidakTerjadwalNama, array_values($missingStudents));

                foreach ($mahasiswaData as $mhs) {
                    $mhsNama = $mhs['nama'] ?? '';

                    if (in_array($mhsNama, $allUnscheduledNames)) {
                        // Cari alasan spesifik
                        $reason = $this->determineUnscheduledReason($mhs, $result);

                        if (in_array($mhsNama, $missingStudents)) {
                            $reason = "⚠️ Data tidak dikembalikan oleh API (kemungkinan bug di OR-Tools). " . $reason;
                        }

                        $tidakTerjadwalData[] = [
                            'nama' => $mhsNama,
                            'nim' => $mhs['nim'] ?? '-',
                            'judul' => $mhs['judul'] ?? '-',
                            'bidang' => $mhs['bidang'] ?? '-',
                            'pembimbing1' => $mhs['pembimbing1'] ?? '-',
                            'pembimbing2' => $mhs['pembimbing2'] ?? '-',
                            'reason' => $reason
                        ];
                    }
                }

                // Jika API sudah mengirim detail lengkap (format baru dari Python)
                if (isset($result['unscheduled_details']) && !empty($result['unscheduled_details'])) {
                    // Merge dengan data yang sudah ada, prioritaskan dari API
                    $apiDetails = $result['unscheduled_details'];
                    $apiNames = array_column($apiDetails, 'nama');

                    // Remove duplicates, prioritaskan data dari API
                    $tidakTerjadwalData = array_filter($tidakTerjadwalData, function ($item) use ($apiNames) {
                        return !in_array($item['nama'], $apiNames);
                    });

                    $tidakTerjadwalData = array_merge($apiDetails, $tidakTerjadwalData);
                }

                Log::info('Final Unscheduled Count', [
                    'dari_api' => count($tidakTerjadwalNama),
                    'missing' => count($missingStudents),
                    'total_unscheduled' => count($tidakTerjadwalData)
                ]);

                // 5. Validasi: total harus match
                $totalAccounted = count($jadwal) + count($tidakTerjadwalData);
                $totalInput = count($mahasiswaData);

                if ($totalAccounted !== $totalInput) {
                    Log::warning('⚠️ MISMATCH DETECTED!', [
                        'input' => $totalInput,
                        'terjadwal' => count($jadwal),
                        'tidak_terjadwal' => count($tidakTerjadwalData),
                        'total_accounted' => $totalAccounted,
                        'difference' => $totalInput - $totalAccounted
                    ]);
                }

                Log::info('Jadwal generated', [
                    'status' => $status,
                    'total' => $totalMahasiswa,
                    'terjadwal' => count($jadwal),
                    'tidak_terjadwal' => count($tidakTerjadwalData)
                ]);

                // Build success message
                $message = count($jadwal) . " dari {$totalInput} mahasiswa berhasil dijadwalkan";

                if ($status === 'partial' && !empty($tidakTerjadwalData)) {
                    $message = "⚠️ Penjadwalan Parsial: " . count($jadwal) . " dari {$totalInput} mahasiswa berhasil dijadwalkan";
                }

                // Warning jika ada mismatch
                if ($totalAccounted !== $totalInput) {
                    $message .= " | ⚠️ Ada perbedaan data ({$totalAccounted} vs {$totalInput})";
                }

                $alertType = $status === 'success' ? 'success' : 'warning';

                // Simpan ke session dengan data lengkap
                return redirect()->route('mahasiswa.jadwal')
                    ->with('jadwal_result', $jadwal)
                    ->with('unscheduled_students', $tidakTerjadwalData)
                    ->with('jadwal_stats', [
                        'status' => $status,
                        'total' => $totalInput,
                        'terjadwal' => count($jadwal),
                        'tidak_terjadwal_count' => count($tidakTerjadwalData),
                        'has_mismatch' => $totalAccounted !== $totalInput
                    ])
                    ->with($alertType, $message);

            } else {
                // Handle failed/infeasible status
                $message = $result['message'] ?? 'Penjadwalan gagal';
                $tidakTerjadwalData = [];

                // Extract unscheduled students from debug_info
                if (isset($result['debug_info']['issues'])) {
                    foreach ($result['debug_info']['issues'] as $issue) {
                        $mahasiswaNama = $issue['mahasiswa'] ?? 'Unknown';

                        // Cari detail dari mahasiswaData
                        $mhsDetail = collect($mahasiswaData)->firstWhere('nama', $mahasiswaNama);

                        $problems = isset($issue['problems'])
                            ? implode('; ', array_slice($issue['problems'], 0, 3))
                            : 'Tidak dapat menemukan slot yang sesuai';

                        $tidakTerjadwalData[] = [
                            'nama' => $mahasiswaNama,
                            'nim' => $mhsDetail['nim'] ?? '-',
                            'judul' => $mhsDetail['judul'] ?? '-',
                            'bidang' => $mhsDetail['bidang'] ?? '-',
                            'pembimbing1' => $mhsDetail['pembimbing1'] ?? '-',
                            'pembimbing2' => $mhsDetail['pembimbing2'] ?? '-',
                            'reason' => $problems
                        ];
                    }
                }

                // Jika tidak ada di debug_info, tampilkan semua mahasiswa sebagai tidak terjadwal
                if (empty($tidakTerjadwalData)) {
                    foreach ($mahasiswaData as $mhs) {
                        $tidakTerjadwalData[] = [
                            'nama' => $mhs['nama'] ?? '-',
                            'nim' => $mhs['nim'] ?? '-',
                            'judul' => $mhs['judul'] ?? '-',
                            'bidang' => $mhs['bidang'] ?? '-',
                            'pembimbing1' => $mhs['pembimbing1'] ?? '-',
                            'pembimbing2' => $mhs['pembimbing2'] ?? '-',
                            'reason' => 'Penjadwalan gagal - tidak dapat menemukan solusi feasible'
                        ];
                    }
                }

                Log::warning('Scheduling failed', ['result' => $result]);

                return redirect()->route('mahasiswa.jadwal')
                    ->with('unscheduled_students', $tidakTerjadwalData)
                    ->with('error', $message);
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation Error', ['errors' => $e->errors()]);
            return back()->withErrors($e->errors())->withInput();

        } catch (\Exception $e) {
            Log::error('Unexpected Error', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
            ]);

            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        } finally {
            Log::info('=== END GENERATE JADWAL ===');
        }
    }

    /**
     * Determine specific reason why student cannot be scheduled
     */
    private function determineUnscheduledReason($mahasiswa, $apiResponse)
    {
        // Cek apa ada informasi spesifik di debug_info
        if (isset($apiResponse['debug_info']['issues'])) {
            foreach ($apiResponse['debug_info']['issues'] as $issue) {
                if ($issue['mahasiswa'] === $mahasiswa['nama']) {
                    if (isset($issue['problems']) && !empty($issue['problems'])) {
                        return implode('; ', array_slice($issue['problems'], 0, 3));
                    }
                }
            }
        }

        $reasons = [];

        // Cek jika tidak dosen dengan bidang keahlian
        $bidang = $mahasiswa['bidang'] ?? '';
        if (!empty($bidang)) {
            $reasons[] = "Kemungkinan tidak ada dosen penguji dengan keahlian di bidang {$bidang}";
        }

        // Cek jika pembimbing tidak lengkap
        if (empty($mahasiswa['pembimbing1']) || empty($mahasiswa['pembimbing2'])) {
            $reasons[] = "Data pembimbing tidak lengkap";
        }

        // alasan default
        if (empty($reasons)) {
            $reasons[] = "Tidak dapat menemukan kombinasi optimal antara dosen penguji, waktu, dan ruangan yang tersedia";
        }

        return implode('; ', $reasons);
    }

    /**
     * Prepare mahasiswa data untuk API
     */
    private function prepareMahasiswaData()
    {
        return Mahasiswa::with(['keahlian', 'dosens'])
            ->get()
            ->map(function ($mahasiswa) {
                $pembimbings = $mahasiswa->dosens->values();

                return [
                    'nama' => $mahasiswa->nama,
                    'judul' => $mahasiswa->skripsi,
                    'bidang' => $mahasiswa->keahlian->nama ?? 'General',
                    'pembimbing1' => $pembimbings[0]->nama ?? null,
                    'pembimbing2' => $pembimbings[1]->nama ?? null,
                ];
            })
            ->filter(function ($mhs) {
                // Filter hanya mahasiswa dengan 2 pembimbing lengkap
                return !is_null($mhs['pembimbing1']) && !is_null($mhs['pembimbing2']);
            })
            ->values()
            ->toArray();
    }

    /**
     * Prepare dosen data untuk API
     */
    private function prepareDosenData()
    {
        return Dosen::with('keahlians')
            ->get()
            ->map(function ($dosen) {
                return [
                    'nama' => $dosen->nama,
                    'bidang' => $dosen->keahlians->pluck('nama')->toArray()
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * Prepare availabilitas dosen untuk API
     */
    private function prepareAvailabilitasData()
    {
        return Dosen::with('sesis')
            ->get()
            ->map(function ($dosen) {
                return [
                    'nama' => $dosen->nama,
                    'available' => $dosen->sesis->map(function ($sesi) {
                        $jam = substr($sesi->jam_sesi, 0, 5);
                        $jam = str_replace(':', '.', $jam);

                        $jamMulai = strtotime($sesi->jam_sesi);
                        $jamSelesai = date('H.i', strtotime('+2 hours', $jamMulai));

                        return $sesi->hari . ' ' . $jam . '-' . $jamSelesai;
                    })->toArray()
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * Prepare ruangan data
     */
    private function prepareRuanganData()
    {
        return Ruangan::pluck('nama')->toArray();
    }

    /**
     * Get unique hari dari sesi
     */
    private function getUniqueHari()
    {
        return Sesi::select('hari')
            ->distinct()
            ->orderByRaw("FIELD(hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu')")
            ->pluck('hari')
            ->toArray();
    }

    /**
     * Get unique waktu dari sesi
     */
    private function getUniqueWaktu()
    {
        return Sesi::all()
            ->map(function ($sesi) {
                $jam = substr($sesi->jam_sesi, 0, 5);
                $jam = str_replace(':', '.', $jam);

                $jamMulai = strtotime($sesi->jam_sesi);
                $jamSelesai = date('H.i', strtotime('+2 hours', $jamMulai));

                return $jam . '-' . $jamSelesai;
            })
            ->unique()
            ->sort()
            ->values()
            ->toArray();
    }

    /**
     * Clear session jadwal
     */
    public function clearJadwal()
    {
        session()->forget(['jadwal_result', 'jadwal_stats']);
        return redirect()->route('mahasiswa.jadwal')
            ->with('success', 'Jadwal berhasil dihapus');
    }

    /**
     * Export jadwal ke JSON
     */
    public function exportJadwal()
    {
        $jadwalData = session('jadwal_result', []);

        if (empty($jadwalData)) {
            return back()->with('error', 'Tidak ada jadwal untuk di-export');
        }

        $filename = 'jadwal-ujian-' . date('Y-m-d-His') . '.json';

        return response()->json($jadwalData, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Check API health status
     */
    public function apiHealthStatus()
    {
        try {
            $response = Http::timeout(5)->get($this->orToolsUrl . '/health');

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'data' => $response->json(),
                    'url' => $this->orToolsUrl
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'API tidak merespon dengan status: ' . $response->status(),
                'url' => $this->orToolsUrl
            ], 503);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Connection error: ' . $e->getMessage(),
                'url' => $this->orToolsUrl
            ], 503);
        }
    }
}