<?php

namespace App\Models\Schemas;

/**
 * @OA\Schema(
 *   oneOf={
 *     @OA\Schema(
 *       ref="#/components/schemas/UpsertTime"
 *     )
 *   },
 *   required={"id", "name", "email"},
 * )
 */
class Store
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
}

/**
 * @OA\Schema(
 *   required={"name", "email", "password", "password_confirmation"},
 *   oneOf={@OA\Schema(ref="#/components/schemas/LoginStoreInput")}
 * )
 */
class RegisterStoreInput
{
    /**
     * @OA\Property()
     * @var string
     */
    public $name;

    /**
     * @OA\Property(format="password")
     * @var string
     */
    public $password_confirmation;
}

/**
 * @OA\Schema(required={"email", "password"})
 */
class LoginStoreInput
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
