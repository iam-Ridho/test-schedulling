<?php

namespace App\Http\Controllers;

use App\Models\Keahlian;
use Illuminate\Http\Request;

class KeahlianController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $allKeahlian = Keahlian::all();
        return view('keahlian.index', compact('allKeahlian'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('keahlian.create');
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
        Keahlian::create($validatedData);

        //redirect
        return redirect()->route('keahlian.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Keahlian $keahlian)
    {
        return view('keahlian.show', compact('keahlian'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Keahlian $keahlian)
    {
        return view('keahlian.edit', compact('keahlian'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Keahlian $keahlian)
    {
        //validasi data
        $validatedData = $request->validate([
            'nama' => 'required|max: 100',
        ]);

        //update data
        $keahlian->update($validatedData);

        //redirect
        return redirect()->route('keahlian.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Keahlian $keahlian)
    {
        $keahlian->delete();
        
        return redirect()->route('keahlian.index');
    }
}
