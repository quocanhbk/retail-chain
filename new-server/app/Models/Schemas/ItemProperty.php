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
     * @var integer
     */
    public $id;

    /**
     * @OA\Property()
     * @var integer
     */
    public $quantity;

    /**
     * @OA\Property()
     * @var integer
     */
    public $sell_price;

    /**
     * @OA\Property()
     * @var integer
     */
    public $base_price;

    /**
     * @OA\Property(nullable=true)
     * @var integer
     */
    public $last_purchase_price;

    /**
     * @OA\Property()
     * @var integer
     */
    public $item_id;

    /**
     * @OA\Property()
     * @var integer
     */
    public $branch_id;
}
