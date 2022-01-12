<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class BranchController extends Controller {

    public function create(Request $request) {
        $store_id = Auth::guard('stores')->user()->id;
        $data = $request->all();
        $rules = [
            'name' => ['required', 'string', 'max:255', Rule::unique('branches')->where('store_id', $store_id)],
            'address' => ['required', 'string', 'max:1000'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:5012'],
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 400);
        }

        $isExist = $request->hasFile('image');

        // replace space in branch name with underscore
        $image_name = $store_id . str_replace(' ', '_', $data['name']);

        $path = $isExist
            ? $request->file('image')->storeAs('branches', $image_name . "." . $request->file('image')->getClientOriginalExtension() )
            : 'branches/default.jpg';

        $branch = Branch::create([
            'name' => $data['name'],
            'address' => $data['address'],
            'store_id' => $store_id,
            'image' => $path,
        ]);

        return response()->json($branch);
    }

    public function getBranchImage($filePath) {
        if (!Storage::exists($filePath)) {
            return response()->json([
                'message' => 'File not found.',
            ], 404);
        }
        return response()->file(storage_path('app' . DIRECTORY_SEPARATOR . $filePath));
    }

    public function getBranches(Request $request) {
        $store_id = Auth::guard('stores')->user()->id;

        $search_text = $request->query('search') ?? '';

        // search branch by name, address
        $branches = Branch::where('store_id', $store_id)
            ->where(function ($query) use ($search_text) {
                $query->where('name', 'like', '%' . $search_text . '%')
                    ->orWhere('address', 'like', '%' . $search_text . '%');
            })
            ->get();

        return response()->json($branches);
    }

    public function getBranch(Request $request, $branch_id) {
        $store_id = Auth::guard('stores')->user()->id;

        $branch = Branch::with(['employments' => function($query) {
            $query->where('to', null)->with('employee');
        }])->where('store_id', $store_id)->where('id', $branch_id)->first();
        if (!$branch) {
            return response()->json([
                'message' => 'Branch not found.',
            ], 404);
        }

        return response()->json($branch);
    }

    public function update(Request $request, $branch_id) {
        $store_id = Auth::guard('stores')->user()->id;

        $data = $request->all();
        error_log($branch_id);
        $data['branch_id'] = $branch_id;
        $rules = [
            'branch_id' => ['required', 'integer', Rule::exists('branches', 'id')->where('store_id', $store_id)],
            'name' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:5012'],
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 400);
        }

        $branch = Branch::where('store_id', $store_id)->where('id', $branch_id)->first();

        $isExist = $request->hasFile('image');
        // replace space in branch name with underscore
        $image_name = $store_id . str_replace(' ', '_', $data['name']);
        $path = $isExist
            ? $request->file('image')->storeAs('branches', $image_name . "." . $request->file('image')->getClientOriginalExtension() )
            : $branch->image;

        $branch->name = $data['name'] ?? $branch->name;
        $branch->address = $data['address'] ?? $branch->address;
        $branch->image = $path;
        $branch->save();

        return response()->json($branch);
    }

    public function delete(Request $request, $branch_id) {
        $store_id = Auth::guard('stores')->user()->id;
        // make sure store owns branch
        $branch = Branch::where('store_id', $store_id)->where('id', $branch_id)->first();
        if (!$branch) {
            return response()->json([
                'message' => 'Branch not found.',
            ], 404);
        }
        $branch->delete();

        return response()->json($branch);
    }
}
