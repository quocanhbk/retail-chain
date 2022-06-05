<?php

namespace App\Models\Schemas;

class Other
{
}

/**
 * @OA\Schema(
 *   required={"created_at", "updated_at"}
 * )
 */
class UpsertTime
{
    /**
     * @OA\Property()
     * @var string
     */
    public $created_at;

    /**
     * @OA\Property()
     * @var string
     */
    public $updated_at;
}
