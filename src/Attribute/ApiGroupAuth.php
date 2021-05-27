<?php

namespace App\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS)]
class ApiGroupAuth
{
    public function __construct(public array $groups)
    {
    }
}