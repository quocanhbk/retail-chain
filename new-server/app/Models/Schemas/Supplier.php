<?php

namespace App\Models\Schemas;

/**
 * @OA\Schema(
 *   oneOf={
 *     @OA\Schema(ref="#/components/schemas/UpsertTime")
 *   },
 *   required={"id", "name", "code", "address", "phone", "email", "tax_number", "note", "store_id"},
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
     * @var integer
     */
    public $store_id;

    /**
     * @OA\Property()
     * @var string
     */
    public $name;

    /**
     * @OA\Property()
     * @var string
     */
    public $code;

    /**
     * @OA\Property(nullable=true)
     * @var string
     */
    public $address;

    /**
     * @OA\Property(nullable=true)
     * @var string
     */
    public $phone;

    /**
     * @OA\Property(nullable=true)
     * @var string
     */
    public $email;

    /**
     * @OA\Property(nullable=true)
     * @var string
     */
    public $tax_number;

    /**
     * @OA\Property(nullable=true)
     * @var string
     */
    public $note;
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
