<?php

namespace App\Models\Schemas;

/**
 * @OA\Schema(
 *   oneOf={@OA\Schema(ref="#/components/schemas/UpsertTime")},
 *   required={"id", "code", "name", "phone", "email"}
 * )
 */
class Customer
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
    public $code;

    /**
     * @OA\Property()
     *
     * @var string
     */
    public $name;

    /**
     * @OA\Property(nullable=true)
     *
     * @var string
     */
    public $phone;

    /**
     * @OA\Property(nullable=true)
     *
     * @var string
     */
    public $email;
}

/**
 * @OA\Schema(
 *   oneOf={@OA\Schema(ref="#/components/schemas/UpsertCustomerInput")},
 *   required={"name"}
 * )
 */
class CreateCustomerInput
{
}

/**
 * @OA\Schema()
 */
class UpsertCustomerInput
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
    public $phone;

    /**
     * @OA\Property()
     *
     * @var string
     */
    public $email;
}
