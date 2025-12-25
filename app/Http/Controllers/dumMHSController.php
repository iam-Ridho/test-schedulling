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

class dumMHSController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    private $orToolsUrl;

    public function __construct(){
        $this->orToolsUrl = env('OR_TOOLS_API_URL', 'http://127.0.0.1:8000');
    }

    public function index()
    {
        $allMahasiswa = Mahasiswa::with('keahlians')->get();
        return view('mahasiswa.index', compact('allMahasiswa'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $keahlian = Keahlian::all();
        $dosen = [];
        return view('mahasiswa.create', compact('keahlian', 'dosen'));
    }

    public function getDosenByKeahlian($keahlianId)
    {
        $dosen = Dosen::whereHas('keahlians', function($query) use ($keahlianId) {
            $query->where('keahlians.id', $keahlianId);
        })->get(['id', 'nama', 'nip']);

        return response()->json($dosen);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //validasi data
        $validatedData = $request->validate([
            'nama' => 'required|max:100',
            'nim' => 'required|max:45',
            'skripsi' => 'required|max:300',
            'dosen_pembimbing' => 'required|array|min:2|max:2',
            'dosen_pembimbing.*' => 'exists:dosens,id',
            'keahlian' => 'required|exists:keahlians,id',
        ]);

        //simpan data
        $mahasiswa = Mahasiswa::create([
            'nama' => $validatedData['nama'],
            'nim' => $validatedData['nim'],
            'skripsi' => $validatedData['skripsi'],
            'keahlian_id' => $validatedData['keahlian'],
        ]);

        $mahasiswa->dosens()->attach($validatedData['dosen_pembimbing']);

        //redirect
        return redirect()->route('mahasiswa.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Mahasiswa $mahasiswa)
    {
        return view('mahasiswa.show', compact('mahasiswa'));
        
    }
    
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Mahasiswa $mahasiswa)
    {
        $keahlian = Keahlian::all();
        $dosen = [];
        $selectedKeahlian = $mahasiswa->keahlian_id;
        $selectedDosen = $mahasiswa->dosens->pluck('id')->toArray();
        return view('mahasiswa.edit', compact('mahasiswa', 'keahlian', 'dosen', 'selectedKeahlian', 'selectedDosen'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Mahasiswa $mahasiswa)
    {
        //validasi data
        $validatedData = $request->validate([
            'nama' => 'required|max:100',
            'nim' => 'required|max:45',
            'skripsi' => 'required|max:300',
            'dosen_pembimbing' => 'required|array|min:2|max:2',
            'dosen_pembimbing.*' => 'exists:dosens,id',
            'keahlian' => 'required|exists:keahlians,id',
        ]);

        //simpan data
        $mahasiswa->fill([
            'nama' => $validatedData['nama'],
            'nim' => $validatedData['nim'],
            'skripsi' => $validatedData['skripsi'],
            'keahlian_id' => $validatedData['keahlian'],
        ]);
        
        $mahasiswa->save();

        $mahasiswa->dosens()->sync($validatedData['dosen_pembimbing']);

        //redirect
        return redirect()->route('mahasiswa.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Mahasiswa $mahasiswa)
    {
        $mahasiswa->delete();
        
        return redirect()->route('mahasiswa.index');
    }

    /**INTEGRASI DENGAN OR TOOLS */

    public function jadwalIndex()
    {
        $mahasiswas = Mahasiswa::with(['keahlians', 'dosens.sesis'])->get();

        return view('mahasiswa.jadwal', [
            'mahasiswas' => $mahasiswas,
            'mahasiswaCount' => $mahasiswas->count()
        ]);
    }

    /**Generate Jadwal */
    public function generateJadwal(Request $request)
    {
        try {
            $payload = [
                'mahasiswa' => $this->prepareMahasiswaData(),
                'dosen' => $this->prepareDosenData(),
                'availabilitas_dosen' => $this->prepareAvailabilitasData(),
                'ruangan' => $this->prepareRuanganData(),
                'hari' => $this->getUniqueHari(),
                'waktu' => $this->getUniqueWaktu(),
                'max_time_seconds' => $request->input('max_time_seconds', 60)
            ];

            Log::info('Sending to API', $payload);

            // Send Request
            $response = Http::timeout(120)->post($this->orToolsUrl . '/api/penjadwalan-skripsi', $payload);
            
            if($response->successful()) {
                $result = $response->json();

                if ($result['status'] === 'success') {
                    session(['jadwal_result' => $result['jadwal']]);
                
                return redirect()->route('mahasiswa.jadwal')
                    ->with('success', $result['message'] . ' (' . $result['mahasiswa_terjadwal'] . '/' . $result['total_mahasiswa'] . ')')
                    ->with('jadwal_data', $result['jadwal']);
                } else {
                    return back()->with('warning', $result['message']);
                }
            }

            return back()->with('error', 'Failed. Status: ' . $response->status());

        } catch (\Exception $e) {
            Log::error('OR-TOOLS API Error: ' .  getMessage());
            return back()->with('error', 'Something Error: ' . getMesssage());
        }
    }

    // Prepare data for API
    private function prepareMahasiswaData()
    {
        return Mahasiswa::with(['keahlians', 'dosens'])
            ->get()
            ->map(function ($mahasiswa) {
                $pembimbings = $mahasiswa->dosens->values();
                
                return [
                    'nama' => $mahasiswa->nama,
                    'judul' => $mahasiswa->skripsi,
                    'bidang' => $mahasiswa->keahlians->nama ?? 'General',
                    'pembimbing1' => $pembimbings[0]->nama ?? null,
                    'pembimbing2' => $pembimbings[1]->nama ?? null,
                ];
            })
            ->filter(function ($mhs) {
                return !is_null($mhs['pembimbing1']) && !is_null($mhs['pembimbing2']);
            })
            ->values()
            ->toArray();
    }

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
            ->toArray();
    }

    private function prepareAvailabilitasData()
    {
        return Dosen::with('sesis')
            ->get()
            ->map(function ($dosen) {
                return [
                    'nama' => $dosen->nama,
                    'available' => $dosen->sesis->map(function ($sesi) {
                        return $sesi->format_api;
                    })->toArray()
                ];
            })
            ->toArray();
    }

    private function prepareRuanganData()
    {
        return Ruangan::pluck('nama')->toArray();
    }

    private function getUniqueHari()
    {
        return Sesi::select('hari')->distinct()->pluck('hari')->toArray();
    }

    private function getUniqueWaktu()
    {
        return Sesi::all()->map(function ($sesi) {
            $jam =  substr($sesi->jam_sesi, 0, 5);
            $jam =str_replace(':', '.', $jam);

            $jamMulai = strtotime($sesi->jam_sesi);
            $jamSelesai = date('H.i', strtotime('+2 hours', $jamMulai));

            return $jam . '-' . $jamSelesai;
        })->unique()->values()->toArray();
    }

    //Clear session jadwal
    public function clearJadwal()
    {
        session()->forget('jadwal_result');
        return redirect()->route('mahasiswa.jadwal')->with('success', 'success remove');
    }

    public function exportJadwal() 
    {
        $jadwalData = session('jadwal_result', []);

        if(empty($jadwalData)){
            return back()->with('error', 'Export failed');
        }

        return response()->json($jadwalData, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="jadwal-ujian-"' . date('Y-m-d-His') . '.json"'
        ], JSON_PRETTY_PRINT);
    }

    public function apiHealthStatus()
    {
        try {
            $response = Http::timeout(5)->get($this->orToolsUrl . '/health');
            
            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'data' => $response->json()
                ]);
            }

            return response()->json(['success' => false, 'message' => 'API tidak merespon'], 503);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 503);
        }
    }

}
