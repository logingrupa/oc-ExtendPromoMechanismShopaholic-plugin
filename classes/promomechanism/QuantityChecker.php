<?php declare(strict_types=1);

/**
 * File path: plugins/logingrupa/extendpromomechanism/classes/promomechanism/QuantityChecker.php
 */

namespace Logingrupa\ExtendPromoMechanism\Classes\PromoMechanism;

use October\Rain\Support\Traits\Singleton;
use Lovata\OrdersShopaholic\Classes\PromoMechanism\InterfacePromoMechanism;
use Log;

/**
 * Class QuantityChecker
 * @package Logingrupa\ExtendPromoMechanism\Classes\PromoMechanism
 */
class QuantityChecker
{
    use Singleton;

    /**
     * Check if the total quantity in positions meets the limit
     *
     * @param InterfacePromoMechanism $obMechanism
     * @param array|\Illuminate\Support\Collection $obPositionList
     * @param callable|null $fnCheckPosition Function to check if position is valid
     * @return bool
     */
    public function checkQuantityLimit(InterfacePromoMechanism $obMechanism, $obPositionList, callable $fnCheckPosition = null): bool
    {
        // Get quantity limit value from mechanism properties
        $iQuantityLimit = (int) $obMechanism->getProperty('quantity_limit');
        //Log::info('QuantityChecker::checkQuantityLimit - Quantity limit: ' . $iQuantityLimit);
        
        // If quantity limit is 0, apply to all units regardless of total quantity
        if ($iQuantityLimit === 0) {
            //Log::info('QuantityChecker::checkQuantityLimit - No quantity limit set, applying to all units');
            return true;
        }

        if (empty($obPositionList) || (is_object($obPositionList) && method_exists($obPositionList, 'isEmpty') && $obPositionList->isEmpty())) {
            //Log::info('QuantityChecker::checkQuantityLimit - Position list is empty');
            return false;
        }

        // Calculate total quantity
        $iTotalQuantity = 0;
        foreach ($obPositionList as $obPositionItem) {
            // Skip positions that don't meet criteria
            if ($fnCheckPosition !== null && !$fnCheckPosition($obPositionItem)) {
                continue;
            }

            $iTotalQuantity += $obPositionItem->quantity;
        }

        //Log::info('QuantityChecker::checkQuantityLimit - Total quantity: ' . $iTotalQuantity . ', Limit: ' . $iQuantityLimit);
        
        // Check if total quantity is greater than or equal to the limit
        $bResult = $iTotalQuantity >= $iQuantityLimit;
        //Log::info('QuantityChecker::checkQuantityLimit - Check result: ' . ($bResult ? 'true' : 'false'));
        
        return $bResult;
    }
}