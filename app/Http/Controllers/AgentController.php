<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;


class AgentController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $agents = Agent::paginate($perPage);

        return response()->json([
            'status' => true,
            'message' => 'List agent berhasil diambil.',
            'data' => $agents
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'model' => 'required|string',
            'temperature' => 'nullable|numeric',
            'openrouter_key' => 'nullable|string',
            'description' => 'nullable|string',
            'system_prompt' => 'nullable|string',
            'filters' => 'nullable|array',
            'is_publish' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'data' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $access_key = Str::uuid()->toString();
            $agent = Agent::create([
                ...$validator->validated(),
                'user_id' => optional($request->user())->id ?? 'developer',
                'created_by' => optional($request->user())->id ?? 'developer',
                'is_active' => true,
                'access_key' => $access_key,
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Agent berhasil dibuat.',
                'data' => $agent
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat menyimpan data : ' . $e,
                'data' => null
            ], 500);
        }
    }

    public function show($id)
    {
        $agent = Agent::find($id);

        if (!$agent) {
            return response()->json([
                'status' => false,
                'message' => 'Agent tidak ditemukan.',
                'data' => null
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Detail agent berhasil diambil.',
            'data' => $agent
        ]);
    }

    public function update(Request $request, $id)
    {
        $agent = Agent::find($id);

        if (!$agent) {
            return response()->json([
                'status' => false,
                'message' => 'Agent tidak ditemukan.',
                'data' => null
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'model' => 'required|string',
            'temperature' => 'nullable|numeric',
            'openrouter_key' => 'nullable|string',
            'description' => 'nullable|string',
            'system_prompt' => 'nullable|string',
            'filters' => 'nullable|array',
            'is_active' => 'boolean',
            'is_publish' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'data' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $agent->update([
                ...$validator->validated(),
                'updated_by' => optional($request->user())->id,
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Agent berhasil diperbarui.',
                'data' => $agent
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat update data.',
                'data' => null
            ], 500);
        }
    }

    public function destroy($id)
    {
        $agent = Agent::find($id);

        if (!$agent) {
            return response()->json([
                'status' => false,
                'message' => 'Agent tidak ditemukan.',
                'data' => null
            ], 404);
        }

        DB::beginTransaction();

        try {
            $agent->delete();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Agent berhasil dihapus.',
                'data' => null
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat menghapus agent.',
                'data' => null
            ], 500);
        }
    }
}
