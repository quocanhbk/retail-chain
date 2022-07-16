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
     *
     * @var int
     */
    public $id;

    /**
     * @OA\Property()
     *
     * @var int
     */
    public $shift_id;

    /**
     * @OA\Property()
     *
     * @var int
     */
    public $employee_id;

    /**
     * @OA\Property()
     *
     * @var string
     */
    public $date;

    /**
     * @OA\Property()
     *
     * @var string
     */
    public $note;

    /**
     * @OA\Property(nullable=true)
     *
     * @var bool
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
     *
     * @var int
     */
    public $shift_id;

    /**
     * @OA\Property(
     *   type="array",
     *   @OA\Items(type="integer")
     * )
     *
     * @var array
     */
    public $employee_ids;

    /**
     * @OA\Property()
     *
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
     *
     * @var string
     */
    public $note;

    /**
     * @OA\Property()
     *
     * @var bool
     */
    public $is_absent;
}

/**
 * @OA\Schema(
 *   required={"employee"},
 *   oneOf={@OA\Schema(ref="#/components/schemas/WorkSchedule")}
 * )
 */
class WorkScheduleWithEmployee
{
    /**
     * @OA\Property(ref="#/components/schemas/Employee")
     *
     * @var object
     */
    public $employee;
}

/**
 * @OA\Schema(
 *   required={"shift"},
 *   oneOf={@OA\Schema(ref="#/components/schemas/WorkScheduleWithEmployee")}
 * )
 */
class WorkScheduleWithShiftAndEmployee
{
    /**
     * @OA\Property(ref="#/components/schemas/Shift")
     *
     * @var object
     */
    public $shift;
}
