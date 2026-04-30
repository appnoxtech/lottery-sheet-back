<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\LotteryType;

class LotteryTypeController extends Controller
{
    public function index(Request $request)
    {
        $query = LotteryType::query();
        
        if ($request->has('active_only')) {
            $query->where('is_active', true);
        }
        
        return response()->json($query->orderBy('name')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:lottery_types,name'
        ]);

        $type = LotteryType::create([
            'name' => $validated['name'],
            'is_active' => true
        ]);

        return response()->json($type, 201);
    }

    public function update(Request $request, $id)
    {
        $type = LotteryType::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|unique:lottery_types,name,' . $id,
            'is_active' => 'sometimes|boolean'
        ]);

        $type->update($validated);

        return response()->json($type);
    }

    public function destroy($id)
    {
        $type = LotteryType::findOrFail($id);
        $type->delete();

        return response()->json(['message' => 'Type deleted successfully']);
    }
}
