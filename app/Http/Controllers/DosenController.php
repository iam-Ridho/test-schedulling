<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use App\Models\Keahlian;
use App\Models\Sesi;
use App\Models\Mahasiswa;
use Illuminate\Http\Request;

class DosenController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $allDosen = Dosen::all();
        return view('dosen.index', compact('allDosen'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $keahlian = Keahlian::all();
        $sesi = Sesi::all();
        return view('dosen.create', compact('keahlian', 'sesi'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        //validasi data
        $validatedData = $request->validate([
            'nama' => 'required|max:150',
            'nip' => 'required|unique:dosens|max:100',
            'telepon' => 'required|max:20',
            'keahlian' => 'required|array|min:1',
            'keahlian.*' => 'exists:keahlians,id',
            'sesi' => 'required|array|min:1',
            'sesi.*' => 'exists:sesis,id'
        ]);

        //simpan data
        $dosen = Dosen::create([
            'nama' => $validatedData['nama'],
            'nip' => $validatedData['nip'],
            'telepon' => $validatedData['telepon'],
        ]);


        $dosen->keahlians()->attach($validatedData['keahlian']);
        $dosen->sesis()->attach($validatedData['sesi']);

        //redirect
        return redirect()->route('dosen.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Dosen $dosen)
    {
        $dosen->load(['keahlians', 'sesis']);
        return view('dosen.show', compact('dosen'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Dosen $dosen)
    {
        $keahlian = Keahlian::all();
        $selectedKeahlian = $dosen->keahlians->pluck('id')->toArray();
        $sesi = Sesi::all();
        $selectedSesi = $dosen->sesis->pluck('id')->toArray();

        return view('dosen.edit', compact('dosen', 'keahlian', 'selectedKeahlian', 'sesi', 'selectedSesi'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Dosen $dosen)
    {
        //validasi data
        $validatedData = $request->validate([
            'nama' => 'required|max:150',
            'nip' => 'required|unique:dosens,nip,'.$dosen->id.'|max:100',
            'telepon' => 'required|max:20',
            'keahlian' => 'required|array|min:1',
            'keahlian.*' => 'exists:keahlians,id',
            'sesi' => 'required|array|min:1',
            'sesi.*' => 'exists:sesis,id'
        ]);

        //update data
        $dosen->update([
            'nama' => $validatedData['nama'],
            'nip' => $validatedData['nip'],
            'telepon' => $validatedData['telepon'],
        ]);

        $dosen->keahlians()->sync($validatedData['keahlian']);
        $dosen->sesis()->sync($validatedData['sesi']);

        //redirect
        return redirect()->route('dosen.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Dosen $dosen)
    {
        $dosen->delete();

        return redirect()->route('dosen.index');
    }
}
