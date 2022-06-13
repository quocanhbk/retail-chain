<?php

namespace App\Models\Schemas;

/**
 * @OA\Schema(
 *   required={"id", "category_id", "product_name", "bar_code", "qr_code", "image_url", "branch", "made_in", "unit", "mfg_date", "exp_date", "description", "source_url", "date", "is_duplicate"}
 * )
 */
class DefaultItem
{
    /**
     * @OA\Property()
     * @var integer
     */
    public $id;

    /**
     * @OA\Proeprty()
     * @var integer
     */
    public $category_id;

    /**
     * @OA\Property()
     * @var string
     */
    public $product_name;

    /**
     * @OA\Property()
     * @var string
     */
    public $bar_code;

    /**
     * @OA\Property(nullable=true)
     * @var string
     */
    public $qr_code;

    /**
     * @OA\Property(nullable=true)
     * @var string
     */
    public $image_url;

    /**
     * @OA\Property(nullable=true)
     * @var string
     */
    public $brand;

    /**
     * @OA\Property(nullable=true)
     * @var string
     */
    public $made_in;

    /**
     * @OA\Property(nullable=true)
     * @var string
     */
    public $unit;

    /**
     * @OA\Property(nullable=true)
     * @var string
     */
    public $mfg_date;

    /**
     * @OA\Property(nullable=true)
     * @var string
     */
    public $exp_date;

    /**
     * @OA\Property(nullable=true)
     * @var string
     */
    public $description;

    /**
     * @OA\Property()
     * @var string
     */
    public $source_url;

    /**
     * @OA\Property()
     * @var string
     */
    public $date;

    /**
     * @OA\Property()
     * @var integer
     */
    public $is_duplicate;
}

/**
 * @OA\Schema(
 *   oneOf={@OA\Schema(ref="#/components/schemas/DefaultItem")}
 * )
 */
class DefaultItemWithCategory
{
    /**
     * @OA\Property(ref="#/components/schemas/DefaultCategory")
     * @var object
     */
    public $category;
}

/**
 * @OA\Schema(
 *   required={"id", "name"}
 * )
 */
class DefaultCategory
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
}
