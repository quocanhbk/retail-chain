<?php

namespace App\Models\Schemas;

/**
 * @OA\Schema(
 *   required={"id", "code", "employee_id", "branch_id", "note"},
 *   oneOf={@OA\Schema(ref="#/components/schemas/UpsertTime")}
 * )
 */
class QuantityCheckingSheet
{
    /**
     * @OA\Property()
     *
     * @var integer
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
     * @var integer
     */
    public $employee_id;

    /**
     * @OA\Property()
     *
     * @var integer
     */
    public $branch_id;

    /**
     * @OA\Property()
     *
     * @var string
     */
    public $note;
}

/**
 * @OA\Schema(
 *   required={"employee"},
 *   oneOf={@OA\Schema(ref="#/components/schemas/QuantityCheckingSheet")}
 * )
 */
class QuantityCheckingSheetWithEmployee
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
 *   required={"items"},
 *   oneOf={@OA\Schema(ref="#/components/schemas/QuantityCheckingSheetWithEmployee")}
 * )
 */
class QuantityCheckingSheetDetail
{
    /**
     * @OA\Property(
     *   type="array",
     *   @OA\Items(ref="#/components/schemas/QuantityCheckingItem"),
     * )
     *
     * @var array
     */
    public $items;
}

/**
 * @OA\Schema(required={"items"})
 */
class CreateQuantityCheckingSheetInput
{
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
    public $note;

    /**
     * @OA\Property(
     *   type="array",
     *   @OA\Items(
     *     required={"id", "actual_quantity"},
     *     @OA\Property(property="id", type="integer"),
     *     @OA\Property(property="actual_quantity", type="integer"),
     *   )
     * )
     *
     * @var array
     */
    public $items;
}

/**
 * @OA\Schema(
 *   required={"id", "quantity_checking_sheet_id", "item_id", "expected_quantity", "actual_quantity", "total"},
 *   oneOf={@OA\Schema(ref="#/components/schemas/UpsertTime")},
 * )
 */
class QuantityCheckingItem
{
    /**
     * @OA\Property()
     *
     * @var integer
     */
    public $id;

    /**
     * @OA\Property()
     *
     * @var integer
     */
    public $quantity_checking_sheet_id;

    /**
     * @OA\Property()
     *
     * @var integer
     */
    public $item_id;

    /**
     * @OA\Property()
     *
     * @var integer
     */
    public $expected_quantity;

    /**
     * @OA\Property()
     *
     * @var integer
     */
    public $actual_quantity;

    /**
     * @OA\Property()
     *
     * @var integer
     */
    public $total;
}
