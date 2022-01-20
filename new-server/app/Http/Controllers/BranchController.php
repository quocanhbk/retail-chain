<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Employment;
use App\Traits\EmployeeTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BranchController extends Controller {
    use EmployeeTrait;

    public function create(Request $request) {
        $store_id = Auth::guard('stores')->user()->id;
        $data = $request->all();
        $rules = [
            'name' => ['required', 'string', 'max:255', Rule::unique('branches')->where('store_id', $store_id)],
            'address' => ['required', 'string', 'max:1000'],
            'new_employees' => ['nullable', 'array'],
            'new_employees.*.name' => ['required', 'string', 'max:255'],
            'new_employees.*.email' => ['required', 'string', 'email', 'max:255', Rule::unique('employees')->where('store_id', $store_id)],
            'new_employees.*.password' => ['required', 'string', 'min:6', 'confirmed'],
            'new_employees.*.roles' => ['required', 'array'],
            'new_employees.*.roles.*' => ['required', Rule::in(['manage', 'purchase', 'sale'])],
            'new_employess.*.phone' => ['nullable', 'string', 'max:255'],
            'new_employees.*.birthday' => ['nullable', 'date'],
            'new_employees.*.gender' => ['nullable', 'string'],
            'transfer_employees' => ['nullable', 'array'],
            'transfer_employees.*.id' => ['required', Rule::exists('employees', 'id')->where('store_id', $store_id)],
            'transfer_employees.*.roles' => ['required', 'array'],
            'transfer_employees.*.roles.*' => ['required', Rule::in(['manage', 'purchase', 'sale'])]
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 400);
        }

        $branch = Branch::create([
            'name' => $data['name'],
            'address' => $data['address'],
            'store_id' => $store_id,
            'image' => 'branches/default.jpg',
            'image_key' => Str::uuid(),
        ]);

        // if there are new employees, create them
        if (isset($data['new_employees'])) {
            foreach ($data['new_employees'] as $employee) {
                $employee['branch_id'] = $branch->id;
                $this->createEmployee($store_id, $employee);
            }
        }

        // if there are employees to be transferred, transfer them
        if (isset($data['transfer_employees'])) {
            foreach ($data['transfer_employees'] as $employee) {
                $this->transferEmployee($employee->id, $branch->id, $employee->roles);
            }
        }

        return response()->json($branch);
    }

    public function getBranchImage($image_key) {
        $branch = Branch::where('image_key', $image_key)->first();
        if(!$branch) {
            return response()->json([
                'message' => 'Branch not found.',
            ], 404);
        }

        $file_path = $branch->image;
        if (!Storage::exists($file_path)) {
            return response()->json([
                'message' => 'File not found.',
            ], 404);
        }
        return response()->file(storage_path('app' . DIRECTORY_SEPARATOR . $file_path));
    }

    public function updateBranchImage(Request $request) {
        $store_id = Auth::guard('stores')->user()->id;
        $data = $request->all();
        $rules = [
            'id' => ['required', Rule::exists('branches', 'id')->where('store_id', $store_id)],
            'image' => ['required', 'image', 'max:2048'],
        ];

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 400);
        }

        $branch = Branch::find($data['id']);
        $image_name = $store_id . Str::uuid();
        $path = $request->file('image')->storeAs('branches', $image_name . "." . $request->file('image')->getClientOriginalExtension());

        $branch->image = $path;
        $branch->image_key = Str::uuid();
        $branch->save();
        return response()->json([
            'message' => 'Branch image updated.'
        ]);
    }

    public function getBranches(Request $request) {
        $store_id = Auth::guard('stores')->user()->id;

        $search_text = $request->query('search') ?? '';
        $sort_key = $request->query('sort_key') ?? 'name';
        $sort_order = $request->query('sort_order') ?? 'asc';

        // search branch by name, address
        $branches = Branch::where('store_id', $store_id)
            ->where(function ($query) use ($search_text) {
                $query->where('name', 'like', '%' . $search_text . '%')
                    ->orWhere('address', 'like', '%' . $search_text . '%');
            })
            ->orderBy($sort_key, $sort_order)
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

        $has_image = $request->hasFile('image');

        // delete old image if new image is uploaded
        if ($has_image) {
            $old_image = $branch->image;
            if ($old_image != 'branches/default.jpg') {
                Storage::delete($old_image);
            }
            // change image key when new image is uploaded
            $branch->image_key = Str::uuid();
        }

        $image_name = $store_id . Str::uuid();
        $path = $has_image
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

        $employments = Employment::where('branch_id', $branch_id)->where('to', null)->get();
        // if employments is not empty, return error
        if ($employments->isNotEmpty()) {
            return response()->json([
                'message' => 'Còn nhân viên làm việc tại chi nhánh này.',
            ], 400);
        }

        $terminated_employments = Employment::where('branch_id', $branch_id)->where('to', '!=', null)->get();
        // delete all terminated employments roles and employment
        foreach ($terminated_employments as $employment) {
            $employment->roles()->delete();
            $employment->delete();
        }

        $branch->delete();

        return response()->json($branch);
    }
}
