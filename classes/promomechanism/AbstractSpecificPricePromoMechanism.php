<?php declare(strict_types=1);

/**
 * File path: plugins/logingrupa/extendpromomechanism/classes/promomechanism/AbstractSpecificPricePromoMechanism.php
 */

namespace Logingrupa\ExtendPromoMechanism\Classes\PromoMechanism;

use Lovata\OrdersShopaholic\Classes\PromoMechanism\AbstractPromoMechanism;
use Lovata\OrdersShopaholic\Classes\PromoMechanism\InterfacePromoMechanism;
use Lovata\Toolbox\Classes\Helper\PriceHelper;
use Log;

/**
 * Class AbstractSpecificPricePromoMechanism
 * @package Logingrupa\ExtendPromoMechanism\Classes\PromoMechanism
 * 
 * This class extends the standard AbstractPromoMechanism to provide functionality
 * for setting specific target prices instead of just applying fixed discounts.
 * In this implementation, the discount_value represents the target price we want to achieve.
 */
abstract class AbstractSpecificPricePromoMechanism extends AbstractPromoMechanism implements InterfacePromoMechanism
{
    /**
     * Apply fixed discount to reach a specific target price
     * In this case, the discount_value represents the target price we want to achieve
     * 
     * @param float $fPrice Current price before discount
     * @return float The discounted price
     */
    protected function applyFixedDiscount($fPrice)
    {
        //Log::info('AbstractSpecificPricePromoMechanism::applyFixedDiscount - Starting with price: ' . $fPrice . ', target price: ' . $this->fDiscountValue);
        
        if ($this->bIncrease) {
            // Keep the original behavior for increases
            $fPrice = PriceHelper::round($fPrice + $this->fDiscountValue);
            //Log::info('AbstractSpecificPricePromoMechanism::applyFixedDiscount - Increase mode, new price: ' . $fPrice);
        } else {
            // If the current price is higher than target price, set it to target price
            if ($fPrice > $this->fDiscountValue) {
                $fPrice = PriceHelper::round($this->fDiscountValue);
                //Log::info('AbstractSpecificPricePromoMechanism::applyFixedDiscount - Applied target price: ' . $fPrice);
            } else {
                //Log::info('AbstractSpecificPricePromoMechanism::applyFixedDiscount - Current price is already lower than target, keeping: ' . $fPrice);
            }
            
            // Ensure price doesn't go below zero (safety check)
            if ($fPrice < 0) {
                $fPrice = 0;
                //Log::info('AbstractSpecificPricePromoMechanism::applyFixedDiscount - Price was negative, set to 0');
            }
        }

        return $fPrice;
    }
    
    /**
     * Get property value with a default if not set
     * 
     * @param string $sField Field name
     * @param mixed $mDefault Default value
     * @return mixed
     */
    protected function getPropertyWithDefault($sField, $mDefault = null)
    {
        $mValue = $this->getProperty($sField);
        return $mValue !== null ? $mValue : $mDefault;
    }
}