<?php

class ProductController
{
    public static function index($group)
    {

        if ( $group !== null && !is_numeric($group) ) {
            throw new Exception('Wrong group');
        }

        [$parentGroups, $childGroups, $mainGroup, $products] = $group !== null ? ProductRepository::index($group) : ProductRepository::index(DEFAULT_GROUP);

        $productGroups = [
            'parentGroups' => $parentGroups,
            'mainGroup' => [$mainGroup],
            'childGroups' => $childGroups,
            'products' => $products
        ];

        View::render('template', ['productGroups' => $productGroups]);
        
    }
}