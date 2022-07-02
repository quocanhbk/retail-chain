<?php

namespace App\Traits;

use App\Mail\EmployeeAccountCreated;
use App\Models\Employee;
use App\Models\Employment;
use App\Models\EmploymentRole;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

trait EmployeeTrait
{
    public function createEmployee($store_id, $data, $avatar = null)
    {
        $avatar_image_path = isset($avatar)
            ? $avatar->storeAs(
                "images/{$store_id}/employees",
                $store_id . Str::uuid() . "." . $avatar->getClientOriginalExtension()
            )
            : null;

        // generate a random password with 8 characters
        $password = Str::random(8);

        $employee = Employee::create([
            "store_id" => $store_id,
            "name" => $data["name"],
            "email" => $data["email"],
            "password" => Hash::make($password),
            "phone" => $data["phone"] ?? null,
            "birthday" => $data["birthday"] ?? null,
            "avatar" => $avatar_image_path,
            "avatar_key" => isset($avatar) ? Str::uuid() : null,
            "gender" => $data["gender"] ?? null,
        ]);

        $employment = Employment::create([
            "employee_id" => $employee->id,
            "branch_id" => $data["branch_id"],
            "from" => date("Y/m/d"),
        ]);

        // create employment roles
        foreach ($data["role_ids"] as $role) {
            EmploymentRole::create([
                "employment_id" => $employment->id,
                "role_id" => $role,
            ]);
        }

        // send email to employee only on production
        if (env("APP_ENV") === "production") {
            Mail::to($employee->email)->queue(new EmployeeAccountCreated($employee, $password));
        }

        return $employee;
    }

    public function transferEmployee($employee_id, $branch_id, $role_ids)
    {
        $employee = Employee::find($employee_id);

        if (!$employee) {
            return response()->json(["message" => "Employee not found."], 404);
        }

        $old_employment = $employee->employment;
        $old_employment->to = date("Y/m/d");
        $old_employment->save();

        $employment = Employment::create([
            "employee_id" => $employee_id,
            "branch_id" => $branch_id,
            "from" => date("Y/m/d"),
        ]);

        // update employment roles
        foreach ($role_ids as $role_id) {
            EmploymentRole::create([
                "employment_id" => $employment->id,
                "role_id" => $role_id,
            ]);
        }
    }
}
