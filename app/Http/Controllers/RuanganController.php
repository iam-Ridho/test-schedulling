<?php

namespace App\Http\Controllers;

use App\Models\Ruangan;
use Illuminate\Http\Request;

class RuanganController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $allRuangan = Ruangan::all();
        return view('ruangan.index', compact('allRuangan'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('ruangan.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //validasi data
        $validatedData = $request->validate([
            'nama' => 'required|max: 100',
        ]);

        //simpan data
        Ruangan::create($validatedData);

        //redirect
        return redirect()->route('ruangan.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Ruangan $ruangan)
    {
        return view('ruangan.show', compact('ruangan'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Ruangan $ruangan)
    {
        return view('ruangan.edit', compact('ruangan'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Ruangan $ruangan)
    {
        //validasi data
        $validatedData = $request->validate([
            'nama' => 'required|max: 100',
        ]);

        //update data
        $ruangan->update($validatedData);

        //redirect
        return redirect()->route('ruangan.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Ruangan $ruangan)
    {
        $ruangan->delete();
        
        return redirect()->route('ruangan.index');
    }
}
