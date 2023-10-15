<?php

namespace App\Http\Controllers;

use App\Models\artefacto;
use Illuminate\Http\Request;

class ArtefactoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $a = new artefacto();
        $a->key = $request->key;
        $a->text = $request->key;
        $a->isGroup = 'true';
        $a->loc = $request->loc;
        $a->duration = 9;
        $a->tipo = $request->tipo;
        $a->id_diagrama = $request->id_diagrama;
        $a->save();

        return response()->json([
            'message' => 'Artefacto creado correctamente',
            'artefacto' => $request->text,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(artefacto $artefacto)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(artefacto $artefacto)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, artefacto $artefacto)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(artefacto $artefacto)
    {
        //
    }
}
