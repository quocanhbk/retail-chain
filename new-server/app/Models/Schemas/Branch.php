<?php

namespace App\Models\Schemas;

/**
 * @OA\Schema(
 *   required={"id", "name", "address"},
 *   oneOf={@OA\Schema(ref="#/components/schemas/UpsertTime")}
 * )
 */
class Branch
{
    /**
     * @OA\Property()
     *
     * @var int
     */
    public $id;

    /**
     * @OA\Property()
     *
     * @var string
     */
    public $name;

    /**
     * @OA\Property()
     *
     * @var string
     */
    public $address;
}

/**
 * @OA\Schema(
 *   required={"employments"},
 *   oneOf={@OA\Schema(ref="#/components/schemas/Branch")},
 * )
 */
class BranchDetail
{
    /**
     * @OA\Property(
     *  type="array",
     *  @OA\Items(ref="#/components/schemas/EmploymentDetail")
     * )
     *
     * @var array
     */
    public $employments;
}

/**
 * @OA\Schema(
 *     required={"name", "address"}
 * )
 */
class CreateBranchInput
{
    /**
     * @OA\Property()
     *
     * @var string
     */
    public $name;

    /**
     * @OA\Property()
     *
     * @var string
     */
    public $address;

    /**
     * @OA\Property(
     *      type="array",
     *      @OA\Items(ref="#/components/schemas/TransferredEmployeeInput")
     * )
     *
     * @var array
     */
    public $transfered_employees;

    /**
     * @OA\Property(
     *      type="array",
     *      @OA\Items(ref="#/components/schemas/NewEmployeeInput")
     * )
     *
     * @var array
     */
    public $new_employees;
}

/**
 * @OA\Schema()
 */
class UpdateBranchInput
{
    /**
     * @OA\Property()
     *
     * @var string
     */
    public $name;

    /**
     * @OA\Property()
     *
     * @var string
     */
    public $address;

    /**
     * @OA\Property(format="binary")
     *
     * @var string
     */
    public $image;
}

/**
 * @OA\Schema(
 *   required={"id", "role_ids"}
 * )
 */
class TransferredEmployeeInput
{
    /**
     * @OA\Property()
     *
     * @var int
     */
    public $id;

    /**
     * @OA\Property(
     *   type="array",
     *   @OA\Items(type="integer")
     * )
     *
     * @var array
     */
    public $role_ids;
}

/**
 * @OA\Schema(
 *   required={"name", "email", "password", "role_ids"}
 * )
 */
class NewEmployeeInput
{
    /**
     * @OA\Property()
     *
     * @var string
     */
    public $name;

    /**
     * @OA\Property()
     *
     * @var string
     */
    public $email;

    /**
     * @OA\Property(format="password")
     *
     * @var string
     */
    public $password;

    /**
     * @OA\Property(format="password")
     *
     * @var string
     */
    public $password_confirmation;

    /**
     * @OA\Property(
     *   type="array",
     *   @OA\Items(type="integer")
     * )
     *
     * @var string[]
     */
    public $role_ids;

    /**
     * @OA\Property()
     *
     * @var string
     */
    public $phone;

    /**
     * @OA\Property()
     *
     * @var string
     */
    public $birthday;

    /**
     * @OA\Property()
     *
     * @var string
     */
    public $gender;
}
