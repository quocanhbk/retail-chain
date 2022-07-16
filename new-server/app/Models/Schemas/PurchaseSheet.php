<?php

namespace App\Models\Schemas;

/**
 * @OA\Schema(
 *   required={"id", "code", "employee_id", "branch_id", "supplier_id", "discount", "discount_type", "total", "paid_amount", "note"},
 *   oneOf={@OA\Schema(ref="#/components/schemas/UpsertTime")},
 * )
 */
class PurchaseSheet
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
     * @OA\Property(nullable=true)
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
     * @OA\Property(nullable=true)
     *
     * @var integer
     */
    public $supplier_id;

    /**
     * @OA\Property(nullable=true)
     *
     * @var integer
     */
    public $discount;

    /**
     * @OA\Property(nullable=true, enum={"amount", "percent"})
     *
     * @var string
     */
    public $discount_type;

    /**
     * @OA\Property()
     *
     * @var integer
     */
    public $total;

    /**
     * @OA\Property()
     *
     * @var integer
     */
    public $paid_amount;

    /**
     * @OA\Property()
     *
     * @var string
     */
    public $note;
}

/**
 * @OA\Schema(
 *   required={"supplier", "employee"},
 *   oneOf={@OA\Schema(ref="#/components/schemas/PurchaseSheet")},
 * )
 */
class PurchaseSheetWithSupplierAndEmployee
{
    /**
     * @OA\Property(
     *   oneOf={@OA\Schema(ref="#/components/schemas/Supplier"), @OA\Schema(type="null")},
     * )
     *
     * @var object
     */
    public $supplier;

    /**
     * @OA\Property(
     *   oneOf={@OA\Schema(ref="#/components/schemas/Employee"), @OA\Schema(type="null")},
     * )
     *
     * @var object
     */
    public $employee;
}

/**
 * @OA\Schema(
 *   required={"branch", "items"},
 *   oneOf={@OA\Schema(ref="#/components/schemas/PurchaseSheetWithSupplierAndEmployee")},
 * )
 */
class PurchaseSheetDetail
{
    /**
     * @OA\Property(ref="#/components/schemas/Branch")
     *
     * @var object
     */
    public $branch;

    /**
     * @OA\Property(
     *   type="array",
     *   @OA\Items(ref="#/components/schemas/PurchaseSheetItemDetail"),
     * )
     */
    public $items;
}

/**
 * @OA\Schema(
 *   required={"items"}
 * )
 */
class CreatePurchaseSheetInput
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
     * @var integer
     */
    public $supplier_id;

    /**
     * @OA\Property()
     *
     * @var integer
     */
    public $discount;

    /**
     * @OA\Property(enum={"percent", "amount"})
     *
     * @var string
     */
    public $discount_type;

    /**
     * @OA\Property()
     *
     * @var integer
     */
    public $paid_amount;

    /**
     * @OA\Property()
     *
     * @var string
     */
    public $note;

    /**
     * @OA\Property(
     *   type="array",
     *   @OA\Items(ref="#/components/schemas/PurchaseSheetItemInput")
     * )
     */
    public $items;
}

/**
 * @OA\Schema(
 *   required={"id", "quantity", "price"}
 * )
 */
class PurchaseSheetItemInput
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
    public $quantity;

    /**
     * @OA\Property()
     *
     * @var integer
     */
    public $price;

    /**
     * @OA\Property()
     *
     * @var integer
     */
    public $discount;

    /**
     * @OA\Property(enum={"percent", "amount"})
     *
     * @var string
     */
    public $discount_type;
}

/**
 * @OA\Schema(
 *   required={"id", "item_id", "purchase_sheet_id", "price", "discount", "discount_type", "quantity", "total"},
 *   oneOf={@OA\Schema(ref="#/components/schemas/UpsertTime")},
 * )
 */
class PurchaseSheetItem
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
    public $item_id;

    /**
     * @OA\Property()
     *
     * @var integer
     */
    public $purchase_sheet_id;

    /**
     * @OA\Property()
     *
     * @var integer
     */
    public $quantity;

    /**
     * @OA\Property()
     *
     * @var integer
     */
    public $price;

    /**
     * @OA\Property(nullable=true)
     *
     * @var integer
     */
    public $discount;

    /**
     * @OA\Property(enum={"percent", "amount"}, nullable=true)
     *
     * @var string
     */
    public $discount_type;

    /**
     * @OA\Property()
     *
     * @var integer
     */
    public $total;
}

/**
 * @OA\Schema(
 *   required={"item"},
 *   oneOf={@OA\Schema(ref="#/components/schemas/PurchaseSheetItem")},
 * )
 */
class PurchaseSheetItemDetail
{
    /**
     * @OA\Property(ref="#/components/schemas/Item")
     *
     * @var object
     */
    public $item;
}
