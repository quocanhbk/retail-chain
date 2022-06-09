<?php

namespace App\Models\Schemas;

/**
 * @OA\Schema(
 *   oneOf={
 *     @OA\Schema(ref="#/components/schemas/UpsertTime")
 *   },
 *   required={"id", "name", "code"}
 * )
 */
class Supplier
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
    public $address;

    /**
     * @OA\Property()
     * @var string
     */
    public $code;

    /**
     * @OA\Property()
     * @var string
     */
    public $phone;

    /**
     * @OA\Property()
     * @var string
     */
    public $email;

    /**
     * @OA\Property()
     * @var string
     */
    public $tax_number;

    /**
     * @OA\Property()
     * @var string
     */
    public $note;

    /**
     * @OA\Property()
     * @var integer
     */
    public $creator_id;
}

/**
 * @OA\Schema(
 *   oneOf={@OA\Schema(ref="#/components/schemas/UpdateSupplierInput")},
 *   required={"name", "phone"}
 * )
 */
class CreateSupplierInput
{
}

/**
 * @OA\Schema()
 */
class UpdateSupplierInput
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
    public $address;

    /**
     * @OA\Property()
     * @var string
     */
    public $code;

    /**
     * @OA\Property()
     * @var string
     */
    public $phone;

    /**
     * @OA\Property()
     * @var string
     */
    public $email;

    /**
     * @OA\Property()
     * @var string
     */
    public $tax_number;

    /**
     * @OA\Property()
     * @var string
     */
    public $note;
}
