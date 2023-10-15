<?php

namespace App\Http\Controllers;

use App\Models\invitado;
use Illuminate\Http\Request;

class InvitadoController extends Controller
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(invitado $invitado)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(invitado $invitado)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, invitado $invitado)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(invitado $invitado)
    {
        dd($invitado);
    }
    public function invitadoDelete(Request $request)
    {
        // dd($request);
        $Abandonar = invitado::where('invitado', $request->id_invitado)
        ->where('id_diagrama', $request->id_diagrama)->first()->delete();

        return redirect()->route('diagramador.index');
    }

}
