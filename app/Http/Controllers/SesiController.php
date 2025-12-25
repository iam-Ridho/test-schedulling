<?php

namespace App\Http\Controllers;

use App\Models\Sesi;
use Illuminate\Http\Request;

class SesiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $allSesi = Sesi::all();
        return view('sesi.index', compact('allSesi'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('sesi.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //validasi data
        $validatedData = $request->validate([
            'hari' => 'required',
            'jam_sesi' => 'required',
        ]);

        //simpan data
        Sesi::create($validatedData);

        //redirect
        return redirect()->route('sesi.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Sesi $sesi )
    {
        return view('sesi.show', compact('sesi'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Sesi $sesi)
    {
        return view('sesi.edit', compact('sesi'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Sesi $sesi)
    {
        //validasi data
        $validatedData = $request->validate([
            'hari' => 'required',
            'jam_sesi' => 'required',
        ]);

        //update data
        $sesi->update($validatedData);

        //redirect
        return redirect()->route('sesi.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Sesi $sesi)
    {
        $sesi->delete();
        
        return redirect()->route('sesi.index');
    }
}
