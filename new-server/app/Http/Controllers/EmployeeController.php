<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\Employment;
use App\Models\EmploymentRoles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{

    // Create a new employee
    public function create(Request $request) {
        $store_id = Auth::guard('stores')->user()->id;
        $data = $request->all();
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:employees'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'branch_id' => ['required', 'exists:branches,id'],
            'roles' => ['required', 'array'],
            'phone' => ['nullable', 'string', 'max:255'],
            'avatar_url' => ['nullable', 'string', 'max:1000'],
            'birthday' => ['nullable', 'date'],
            'gender' => ['nullable', 'string']
        ];
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 400);
        }

        // check if branch belong to store
        $branch = Branch::where('id', $data['branch_id'])->where('store_id', $store_id)->first();
        if (!$branch) {
            return response()->json([
                'message' => 'Branch not found.',
            ], 404);
        }

        // check if employee is already in the store
        $employee = Employee::where('store_id', $store_id)->where('email', $data['email'])->first();
        if ($employee) {
            return response()->json([
                'message' => 'Employee already exists.',
            ], 400);
        }

        $employee = Employee::create([
            'store_id' => $store_id,
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'phone' => $request->input('phone'),
            'birthday' => $request->input('birthday'),
            'avatar_url' => $request->input('avatar_url'),
            'gender' => $request->input('gender')
        ]);

        $employment = Employment::create([
            'employee_id' => $employee->id,
            'branch_id' => $data['branch_id'],
            'from' => date("Y/m/d")
        ]);

        // create employment roles
        foreach ($data['roles'] as $role) {
            EmploymentRoles::create([
                'employment_id' => $employment->id,
                'role' => $role
            ]);
        }

        return response()->json($employee);
    }

    // Get all employees
    public function getEmployees(Request $request) {
        $store_id = Auth::guard('stores')->user()->id;
        $employees = Employee::where('store_id', $store_id)->get();
        return response()->json($employees);
    }

    // Get an employee
    public function getEmployee(Request $request, $id) {
        $store_id = Auth::guard('stores')->user()->id;
        $employee = Employee::with(['employment' => function ($query) {
            $query->with(['roles:id,role,employment_id'])->get();
        }])->where('store_id', $store_id)->where('id', $id)->first();
        return response()->json($employee);
    }

    // Login as an employee
    public function login(Request $request) {
        $data = $request->all();
        $rules = [
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:6'],
            'remember' => ['boolean', 'nullable']
        ];

        $validator = Validator::make($data, $rules);
        if ($validator->failed()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 400);
        }

        $remember = $request->input('remember') ? true : false;
        $check = Auth::guard('employees')->attempt([
            'email' => $data['email'],
            'password' => $data['password'],
        ], $remember);

        if (!$check) {
            return response()->json([
                'message' => 'Invalid credentials.',
            ], 401);
        }

        return response()->json(Auth::guard('employees')->user());
    }

    // Logout as an employee
    public function logout(Request $request) {
        Auth::guard('employees')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return response()->json(['message' => 'Logged out.']);
    }

    // Get the current employee
    public function me(Request $request) {
        $id = Auth::guard('employees')->user()->id;
        $employee = Employee::with('employment.branch')->with('employment.roles')->where('id', $id)->first();
        return response()->json($employee);
    }

    // Transfer to other branch
    public function transfer(Request $request) {
        $store_id = Auth::guard('stores')->user()->id;
        $data = $request->all();
        $rules = [
            'employee_id' => ['required', Rule::exists('employees', 'id')->where('store_id', $store_id)],
            'branch_id' => ['required', Rule::exists('branches', 'id')->where('store_id', $store_id)],
            'roles' => ['required', 'array', 'min:1'],
        ];
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 400);
        }

        // employee must currently not working in the destination branch
        $employment = Employment::where('employee_id', $data['employee_id'])
            ->where('branch_id', $data['branch_id'])
            ->where('to', null)
            ->first();

        if ($employment) {
            return response()->json([
                'message' => 'Employee is already working in this branch.',
            ], 400);
        }

        $validator = Validator::make($data, $rules);
        if ($validator->failed()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 400);
        }

        $old_employment = Employment::where('employee_id', $data['employee_id'])->where('to', null)->first();
        $old_employment->to = date("Y/m/d");
        $old_employment->save();

        $new_employment = Employment::create([
            'employee_id' => $data['employee_id'],
            'branch_id' => $data['branch_id'],
            'from' => date("Y/m/d")
        ]);

        // create employment roles
        foreach ($data['roles'] as $role) {
            EmploymentRoles::create([
                'employment_id' => $new_employment->id,
                'role' => $role
            ]);
        }

        $employee = Employee::with(['employment' => function ($query) {
            $query->with(['roles:id,role,employment_id'])->get();
        }])->where('store_id', $store_id)->where('id', $data['employee_id'])->first();

        return response()->json($employee);
    }
}
