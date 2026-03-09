<?php
namespace SwofShipping;

/**
 * An alias class intended to give its name to WooCommerce ' ' section' parameter.
 */
class swof_tree_table_rate extends ShippingMethod
{
    public static function className() {
        return __CLASS__;
    }
}
