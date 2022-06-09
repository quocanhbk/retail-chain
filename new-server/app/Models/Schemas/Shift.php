<?php

namespace App\Models\Schemas;

/**
 * @OA\Schema(
 *   oneOf={@OA\Schema(ref="#/components/schemas/UpsertTime")},
 *   required={"id", "name", "start_time", "end_time"}
 * )
 */
class Shift
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
    public $branch_id;

    /**
     * @OA\Property()
     * @var string
     */
    public $name;

    /**
     * @OA\Property()
     * @var string
     */
    public $start_time;

    /**
     * @OA\Property()
     * @var string
     */
    public $end_time;
}

/**
 * @OA\Schema()
 */
class UpsertShiftInput
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
    public $start_time;

    /**
     * @OA\Property()
     * @var string
     */
    public $end_time;
}

/**
 * @OA\Schema(
 *   oneOf={@OA\Schema(ref="#/components/schemas/UpsertShiftInput")},
 *   required={"name", "start_time", "end_time"}
 * )
 */
class CreateShiftInput
{
}
