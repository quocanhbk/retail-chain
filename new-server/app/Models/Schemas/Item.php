<?php

namespace App\Models\Schemas;

/**
 * @OA\Schema(
 *   oneOf={@OA\Schema(ref="#/components/schemas/UpsertTime")},
 *   required={"id", "store_id", "barcode", "code", "name", "image", "image_key", "category_id"}
 * )
 */
class Item
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
    public $store_id;

    /**
     * @OA\Property()
     *
     * @var string
     */
    public $barcode;

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
    public $image;

    /**
     * @OA\Property(nullable=true)
     *
     * @var string
     */
    public $image_key;

    /**
     * @OA\Property(nullable=true)
     *
     * @var int
     */
    public $category_id;
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
     * @OA\Property(
     *   anyOf={@OA\Schema(ref="#/components/schemas/Category"), @OA\Schema(type="null")},
     * )
     *
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
     *
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
     *
     * @var int
     */
    public $category_id;

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
    public $barcode;

    /**
     * @OA\Property()
     *
     * @var string
     */
    public $name;

    /**
     * @OA\Property(format="binary")
     *
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
     *
     * @var int
     */
    public $id;

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
    public $price;

    /**
     * @OA\Property()
     *
     * @var string
     */
    public $start_date;

    /**
     * @OA\Property()
     *
     * @var string
     */
    public $end_date;
}
