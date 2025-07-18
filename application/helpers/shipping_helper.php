<?php
defined('BASEPATH') or exit('No direct script access allowed');

if (!function_exists('calculate_shipping_fee')) {
    function calculate_shipping_fee($subtotal)
    {
        if ($subtotal > 200) {
            return 0;
        } elseif ($subtotal >= 52 && $subtotal <= 166.59) {
            return 15;
        } elseif($subtotal >= 1) {
            return 20;
        }
        return 0;
    }
}
