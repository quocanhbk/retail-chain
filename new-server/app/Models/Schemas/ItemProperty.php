<?php

namespace App\Models\Schemas;

/**
 * @OA\Schema(
 *   oneOf={@OA\Schema(ref="#/components/schemas/UpsertTime")},
 *   required={"id", "quantity", "sell_price", "base_price", "last_purchase_price", "item_id", "branch_id"}
 * )
 */
class ItemProperty
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
    public $quantity;

    /**
     * @OA\Property()
     *
     * @var int
     */
    public $sell_price;

    /**
     * @OA\Property()
     *
     * @var int
     */
    public $base_price;

    /**
     * @OA\Property(nullable=true)
     *
     * @var int
     */
    public $last_purchase_price;

    /**
     * @OA\Property()
     *
     * @var int
     */
    public $item_id;

    /**
     * @OA\Property()
     *
     * @var int
     */
    public $branch_id;
}
