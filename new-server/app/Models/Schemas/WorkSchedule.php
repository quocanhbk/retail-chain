<?php

namespace App\Models\Schemas;

/**
 * @OA\Schema(
 *   oneOf={@OA\Schema(ref="#/components/schemas/UpsertTime")},
 *   required={"id", "shift_id", "employee_id", "date", "is_absent", "note"}
 * )
 */
class WorkSchedule
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
    public $shift_id;

    /**
     * @OA\Property()
     * @var integer
     */
    public $employee_id;

    /**
     * @OA\Property()
     * @var string
     */
    public $date;

    /**
     * @OA\Property()
     * @var string
     */
    public $note;

    /**
     * @OA\Property(nullable=true)
     * @var boolean
     */
    public $is_absent;
}

/**
 * @OA\Schema(
 *   oneOf={@OA\Schema(ref="#/components/schemas/UpdateWorkScheduleInput")},
 *   required={"shift_id", "employee_ids", "date"}
 * )
 */
class CreateWorkScheduleInput
{
    /**
     * @OA\Property()
     * @var integer
     */
    public $shift_id;

    /**
     * @OA\Property(
     *   type="array",
     *   @OA\Items(type="integer")
     * )
     * @var array
     */
    public $employee_ids;

    /**
     * @OA\Property()
     * @var string
     */
    public $date;
}

/**
 * @OA\Schema()
 */
class UpdateWorkScheduleInput
{
    /**
     * @OA\Property()
     * @var string
     */
    public $note;

    /**
     * @OA\Property()
     * @var boolean
     */
    public $is_absent;
}
