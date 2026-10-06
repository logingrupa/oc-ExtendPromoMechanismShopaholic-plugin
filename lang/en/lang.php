<?php

/**
 * File path: plugins/logingrupa/extendpromomechanism/lang/en/lang.php
 */

return [
    'plugin' => [
        'name' => 'Custom Promo Mechanisms',
        'description' => 'Extends Shopaholic promo mechanisms with custom functionality',
    ],
    'promo_mechanism_type' => [
        'specific_price_by_quantity_discount_position' => 'Set exact price when total quantity ≥ limit',
        'specific_price_by_quantity_discount_position_description' => 'Sets items to a specific target price when the total quantity in the cart is greater than or equal to the specified limit.',
        'bundle_price_discount_position' => 'Bundle price: N units for a fixed total, repeats for every full bundle',
        'bundle_price_discount_position_description' => 'Every full bundle of N qualifying units costs the set total, for example 2 pcs for 35.00. Units from different offers count together; units left over after the last full bundle keep their price.',
    ],
    'field' => [
        'quantity_limit' => 'Quantity limit',
        'target_price' => 'Target price per item',
    ],
];