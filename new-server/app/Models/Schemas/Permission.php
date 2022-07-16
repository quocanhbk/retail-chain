<?php

namespace App\Models\Schemas;

/**
 * @OA\Schema(required={"id", "action_slug", "action"})
 */
class Permission
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
    public $action_slug;

    /**
     * @OA\Property()
     *
     * @var string
     */
    public $action;
}
