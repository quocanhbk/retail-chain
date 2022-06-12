<?php

namespace App\Models\Schemas;

/**
 * @OA\Schema(
 *   oneOf={@OA\Schema(ref="#/components/schemas/UpsertTime")},
 *   required={"id", "name", "description"}
 * )
 */
class ItemCategory
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
    public $name;

    /**
     * @OA\Property(nullable=true)
     * @var string
     */
    public $description;
}

/**
 * @OA\Schema()
 */
class UpsertItemCategoryInput
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
    public $description;
}

/**
 * @OA\Schema(
 *   required={"name"},
 *   oneOf={@OA\Schema(ref="#/components/schemas/UpsertItemCategoryInput")},
 * )
 */
class CreateItemCategoryInput
{
}
