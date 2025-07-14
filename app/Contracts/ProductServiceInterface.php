<?php

namespace App\Contracts;

interface ProductServiceInterface
{
    public function getAllProducts(array $filters);
}
