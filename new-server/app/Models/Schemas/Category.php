<?php

namespace App\Models\Schemas;

/**
 * @OA\Schema(
 *   oneOf={@OA\Schema(ref="#/components/schemas/UpsertTime")},
 *   required={"id", "store_id", "name", "description"}
 * )
 */
class Category
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
    public $name;

    /**
     * @OA\Property(nullable=true)
     *
     * @var string
     */
    public $description;
}

/**
 * @OA\Schema()
 */
class UpsertCategoryInput
{
    /**
     * @OA\Property()
     *
     * @var string
     */
    public $name;

    /**
     * @OA\Property()
     *
     * @var string
     */
    public $description;
}

/**
 * @OA\Schema(
 *   required={"name"},
 *   oneOf={@OA\Schema(ref="#/components/schemas/UpsertCategoryInput")},
 * )
 */
class CreateCategoryInput
{
}

/**
 * @OA\Schema(
 *   oneOf={@OA\Schema(ref="#/components/schemas/Category")},
 * )
 */
class CategoryWithItems
{
    /**
     * @OA\Property(
     *   type="array",
     *   @OA\Items(ref="#/components/schemas/Item")
     * )
     *
     * @var object
     */
    public $items;
}
