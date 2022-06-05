<?php

namespace App\Models\Schemas;

/**
 * @OA\Schema(
 *   oneOf={@OA\Schema(ref="#/components/schemas/UpsertTime")},
 *   required={"id", "employee_id", "branch_id", "from", "to"},
 * )
 */
class Employment
{
    /**
     * @OA\Property()
     * @var integer
     */
    public $id;

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
     * @OA\Property()
     * @var string
     */
    public $from;

    /**
     * @OA\Property(nullable=true)
     * @var string
     */
    public $to;
}

/**
 * @OA\Schema(
 *   oneOf={@OA\Schema(ref="#/components/schemas/Employment")},
 *   required={"roles"}
 * )
 */
class EmploymentWithRoles
{
    /**
     * @OA\Property(type="array", @OA\Items(ref="#/components/schemas/EmploymentRole"))
     * @var array
     */
    public $roles;
}

/**
 * @OA\Schema(
 *   required={"id", "employment_id", "role"}
 * )
 */
class EmploymentRole
{
    /**
     * @OA\Property()
     * @var integer
     */
    public $id;

    /**
     * @OA\Property()
     * @var integer
     */
    public $employment_id;

    /**
     * @OA\Property()
     * @var string
     */
    public $role;
}

/**
 * @OA\Schema(
 *   oneOf={@OA\Schema(ref="#/components/schemas/Employment")},
 *   required={"employee"}
 * )
 */
class EmploymentDetail
{
    /**
     * @OA\Property(ref="#/components/schemas/Employee")
     * @var object
     */
    public $employee;
}
