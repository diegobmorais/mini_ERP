<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('calculate_subtotal')) {
    function calculate_subtotal($cart)
    {
        $subtotal = 0;

        foreach ($cart as $item) {
            $subtotal += (float) $item['unit_price'] * (int) $item['quantity'];
        }

        return $subtotal;
    }
}
