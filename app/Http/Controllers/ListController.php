<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use ListService;

class ListController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json([
            'lists' => [],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     * @TODO → FormRequest
     */
    public function create(Request $request)
    {
        return response()->json([
            'list' => [],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        return response()->json([
            'list' => [],
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        throw new \Exception('Not implemented');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        throw new \Exception('Not implemented');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        return response()->json([
            'list' => [],
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        return response()->json([
            'list' => [],
            'deleted' => true
        ]);
    }
}
