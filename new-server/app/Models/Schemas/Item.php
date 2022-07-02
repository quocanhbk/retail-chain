<?php

namespace App\Models\Schemas;

/**
 * @OA\Schema(
 *   oneOf={@OA\Schema(ref="#/components/schemas/UpsertTime")},
 *   required={"id", "store_id", "barcode", "code", "name", "image", "image_key", "item_category_id"}
 * )
 */
class Item
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
    public $barcode;

    /**
     * @OA\Property()
     * @var string
     */
    public $code;

    /**
     * @OA\Property()
     * @var string
     */
    public $name;

    /**
     * @OA\Property(nullable=true)
     * @var string
     */
    public $image;

    /**
     * @OA\Property(nullable=true)
     * @var string
     */
    public $image_key;

    /**
     * @OA\Property(nullable=true)
     * @var integer
     */
    public $item_category_id;
}

/**
 * @OA\Schema(
 *   oneOf={@OA\Schema(ref="#/components/schemas/Item")},
 *   required={"category"}
 * )
 */
class ItemWithCategory
{
    /**
     * @OA\Property(ref="#/components/schemas/ItemCategory", nullable=true)
     * @var object
     */
    public $category;
}

/**
 * @OA\Schema(
 *   oneOf={@OA\Schema(ref="#/components/schemas/ItemWithCategory")},
 *   required={"properties"}
 * )
 */
class ItemWithProperties
{
    /**
     * @OA\Property(type="array", @OA\Items(ref="#/components/schemas/ItemProperty"))
     * @var array
     */
    public $properties;
}

/**
 * @OA\Schema(
 *   required={"barcode", "name"},
 *   oneOf={@OA\Schema(ref="#/components/schemas/UpsertItemInput")},
 * )
 */
class CreateItemInput
{
}

/**
 * @OA\Schema()
 */
class UpsertItemInput
{
    /**
     * @OA\Property()
     * @var integer
     */
    public $category_id;

    /**
     * @OA\Property()
     * @var string
     */
    public $code;

    /**
     * @OA\Property()
     * @var string
     */
    public $barcode;

    /**
     * @OA\Property()
     * @var string
     */
    public $name;

    /**
     * @OA\Property(format="binary")
     * @var string
     */
    public $image;
}

/**
 * @OA\Schema(
 *   oneOf={@OA\Schema(ref="#/components/schemas/UpsertTime")},
 *   required={"id", "item_id", "price", "start_date", "end_date"}
 * )
 */
class ItemPriceHistory
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
    public $item_id;

    /**
     * @OA\Property()
     * @var float
     */
    public $price;

    /**
     * @OA\Property()
     * @var string
     */
    public $start_date;

    /**
     * @OA\Property()
     * @var string
     */
    public $end_date;
}
