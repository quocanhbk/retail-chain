<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class BranchController extends Controller {

    public function create(Request $request) {
        // retrieve data from form data
        $store_id = Auth::guard('stores')->user()->id;
        $data = $request->all();


        $rules = [
            'name' => ['required', 'string', 'max:255'],
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

        // check if branch name within the same store is used
        $branch = Branch::where('name', $data['name'])->where('store_id', $store_id)->first();
        if ($branch) {
            return response()->json([
                'message' => 'Branch name already exists.',
            ], 400);
        }

        // check if image exist
        $isExist = $request->hasFile('image');
        $path = $isExist
            ? $request->file('image')->storeAs('branches', $store_id . $request->input('name') . "." . $request->file('image')->getClientOriginalExtension() )
            : 'branches/default.jpg';

        $branch = Branch::create([
            'name' => $data['name'],
            'address' => $data['address'],
            'store_id' => Auth::guard('stores')->user()->id,
            'image' => $path,
        ]);

        return response()->json($branch);
    }

    public function getBranchImage($filePath) {
        // $filePath = 'branches' . '/' . $fileName;
        error_log("Got file: " . $filePath);
        if (!Storage::exists($filePath)) {
            return response()->json([
                'message' => 'File not found.',
            ], 404);
        }
        return response()->file(storage_path('app' . DIRECTORY_SEPARATOR . $filePath));
    }

    public function getBranches() {
        $store_id = Auth::guard('stores')->user()->id;

        $branches = Branch::where('store_id', $store_id)->get();

        return response()->json($branches);
    }

    public function getBranch(Request $request, $branch_id) {
        $store_id = Auth::guard('stores')->user()->id;

        $branch = Branch::where('store_id', $store_id)->where('id', $branch_id)->first();
        if (!$branch) {
            return response()->json([
                'message' => 'Unauthorized.',
            ], 401);
        }

        return response()->json($branch);
    }

    public function update(Request $request, $branch_id) {
        $store_id = Auth::guard('stores')->user()->id;
        $data = $request->all();
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:1000'],
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 400);
        }
        // make sure store owns branch
        $branch = Branch::where('store_id', $store_id)->where('id', $branch_id)->first();
        if (!$branch) {
            return response()->json([
                'message' => 'Unauthorized.',
            ], 401);
        }

        $branch->name = $data['name'];
        $branch->address = $data['address'];
        $branch->save();

        return response()->json($branch);
    }

    public function delete(Request $request, $branch_id) {
        $store_id = Auth::guard('stores')->user()->id;
        // make sure store owns branch
        $branch = Branch::where('store_id', $store_id)->where('id', $branch_id)->first();
        $branch->delete();

        return response()->json($branch);
    }
}
