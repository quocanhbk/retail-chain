<?php

namespace App\Models\Schemas;

/**
 * @OA\Schema(
 *   oneOf={@OA\Schema(ref="#/components/schemas/UpsertTime")},
 *   required={"id", "name", "email", "store_id"},
 * )
 */
class Employee
{
    /**
     * @OA\Property()
     * @var integer
     */
    public $id;

    /**
     * @OA\Property()
     * @var string
     */
    public $name;

    /**
     * @OA\Property()
     * @var string
     */
    public $email;

    /**
     * @OA\Property()
     * @var integer
     */
    public $store_id;

    /**
     * @OA\Property()
     * @var string
     */
    public $avatar;

    /**
     * @OA\Property()
     * @var string
     */
    public $avatar_key;

    /**
     * @OA\Property()
     * @var string
     */
    public $phone;

    /**
     * @OA\Property()
     * @var string
     */
    public $birthday;

    /**
     * @OA\Property()
     * @var string
     */
    public $gender;

    /**
     * @OA\Property()
     * @var string
     */
    public $email_verified_at;
}

/**
 * @OA\Schema(
 *   oneOf={@OA\Schema(ref="#/components/schemas/Employee")},
 *   required={"employment"}
 * )
 */
class EmployeeWithEmployment
{
    /**
     * @OA\Property(ref="#/components/schemas/EmploymentWithRoles")
     * @var object
     */
    public $employment;
}

/**
 * @OA\Schema(
 *   required={"name", "email", "password", "password_confirmation", "branch_id", "roles"}
 * )
 */
class CreateEmployeeInput
{
    /**
     * @OA\Property()
     * @var string
     */
    public $name;

    /**
     * @OA\Property()
     * @var string
     */
    public $email;

    /**
     * @OA\Property(format="password")
     * @var string
     */
    public $password;

    /**
     * @OA\Property(format="password")
     * @var string
     */
    public $password_confirmation;

    /**
     * @OA\Property()
     * @var integer
     */
    public $branch_id;

    /**
     * @OA\Property(
     *   type="array",
     *   @OA\Items(
     *     type="string"
     *   )
     * )
     * @var array
     */
    public $roles;

    /**
     * @OA\Property()
     * @var string
     */
    public $phone;

    /**
     * @OA\Property()
     * @var string
     */
    public $birthday;

    /**
     * @OA\Property()
     * @var string
     */
    public $gender;
}

/**
 * @OA\Schema()
 */
class EmployeeAvatar
{
    /**
     * @OA\Property(format="binary")
     * @var string
     */
    public $avatar;
}

/**
 * @OA\Schema(
 *   allOf={
 *     @OA\Schema(ref="#/components/schemas/CreateEmployeeInput"),
 *     @OA\Schema(ref="#/components/schemas/EmployeeAvatar")
 *   }
 * )
 */
class CreateSingleEmployeeInput
{
}

/**
 * @OA\Schema()
 */
class CreateManyEmployeesInput
{
    /**
     * @OA\Property(
     *   type="array",
     *   @OA\Items(ref="#/components/schemas/CreateSingleEmployeeInput"),
     *   required={"employees"}
     * )
     * @var array
     */
    public $employees;
}

/**
 * @OA\Schema()
 */
class UpdateEmployeeInput
{
    /**
     * @OA\Property()
     * @var string
     */
    public $name;

    /**
     * @OA\Property()
     * @var string
     */
    public $email;

    /**
     * @OA\Property(
     *   type="array",
     *   @OA\Items(type="string")
     * )
     * @var array
     */
    public $roles;

    /**
     * @OA\Property()
     * @var string
     */
    public $phone;

    /**
     * @OA\Property()
     * @var string
     */
    public $birthday;

    /**
     * @OA\Property()
     * @var string
     */
    public $gender;
}

/**
 * @OA\Schema(
 *   required={"email", "password"}
 * )
 */
class EmployeeLoginInput
{
    /**
     * @OA\Property()
     * @var string
     */
    public $email;

    /**
     * @OA\Property(format="password")
     * @var string
     */
    public $password;

    /**
     * @OA\Property()
     * @var boolean
     */
    public $remember;
}

/**
 * @OA\Schema(
 *   required={"employee_id", "branch_id", "roles"}
 * )
 */
class TransferEmployeeInput
{
    /**
     * @OA\Property()
     * @var integer
     */
    public $employee_id;

    /**
     * @OA\Property()
     * @var integer
     */
    public $branch_id;

    /**
     * @OA\Property(
     *   type="array",
     *   @OA\Items(type="string")
     * )
     * @var array
     */
    public $roles;
}

/**
 * @OA\Schema(
 *   required={"branch_id", "employees"}
 * )
 */
class TransferManyEmployeesInput
{
    /**
     * @OA\Property()
     * @var integer
     */
    public $branch_id;

    /**
     * @OA\Property(
     *   type="array",
     *   @OA\Items(
     *     @OA\Schema(
     *       required={"employee_id", "roles"},
     *       @OA\Property(property="id", type="integer"),
     *       @OA\Property(property="roles", type="array", @OA\Items(type="string"))
     *     )
     *   )
     * )
     * @var array
     */
    public $employees;
}
