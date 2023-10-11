<?php

namespace App\Http\Controllers;

use App\Models\grupo;
use App\Models\link;
use Illuminate\Http\Request;

class LinkController extends Controller
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
        $link = new link();
        $link->from = $request->from;
        $link->to = $request->to;
        $link->text = $request->text;
        $link->time = $request->time;
        $link->id_diagrama = $request->id_diagrama;
        $link->save();

        $grupo = new grupo();
        $grupo->group = $request->group;
        $grupo->start= $request->start;
        $grupo->duration = $request->duration;
        $grupo->id_diagrama = $request->id_diagrama;
        $grupo->save();

        return response()->json([
            'message' => 'Link y grupo creado correctamente'
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(link $link)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(link $link)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, link $link)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(link $link)
    {
        //
    }
}
