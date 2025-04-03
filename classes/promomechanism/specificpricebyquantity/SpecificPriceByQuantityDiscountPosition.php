<?php declare(strict_types=1);

/**
 * File path: plugins/logingrupa/extendpromomechanism/classes/promomechanism/specificpricebyquantity/SpecificPriceByQuantityDiscountPosition.php
 */

namespace Logingrupa\ExtendPromoMechanism\Classes\PromoMechanism\SpecificPriceByQuantity;

use Lovata\OrdersShopaholic\Classes\PromoMechanism\AbstractPromoMechanism;
use Lovata\OrdersShopaholic\Classes\PromoMechanism\InterfacePromoMechanism;
use Lovata\OrdersShopaholic\Classes\PromoMechanism\ItemPriceContainer;
use Logingrupa\ExtendPromoMechanism\Classes\PromoMechanism\QuantityChecker;
use Lovata\Shopaholic\Classes\Helper\TaxHelper;
use Lovata\Shopaholic\Models\Settings;
use Lovata\Toolbox\Classes\Helper\PriceHelper;
use Log;

/**
 * Class SpecificPriceByQuantityDiscountPosition
 * @package Logingrupa\ExtendPromoMechanism\Classes\PromoMechanism\SpecificPriceByQuantity
 */
class SpecificPriceByQuantityDiscountPosition extends AbstractPromoMechanism implements InterfacePromoMechanism
{
    const LANG_NAME = 'logingrupa.extendpromomechanism::lang.promo_mechanism_type.specific_price_by_quantity_discount_position';

    /**
     * Get mechanism type (position|total_position|shipping|total)
     * @return string
     */
    public static function getType(): string
    {
        return self::TYPE_POSITION;
    }

    /**
     * Check discount condition
     * @param \Lovata\OrdersShopaholic\Classes\PromoMechanism\AbstractPromoMechanismProcessor $obProcessor
     * @param \Lovata\OrdersShopaholic\Classes\Item\CartPositionItem|\Lovata\OrdersShopaholic\Models\OrderPosition|null $obPosition
     * @return bool
     */
    protected function check($obProcessor, $obPosition = null): bool
    {
        Log::info('SpecificPriceByQuantityDiscountPosition::check - Starting check');

        if (!parent::check($obProcessor, $obPosition)) {
            Log::info('SpecificPriceByQuantityDiscountPosition::check - Parent check failed');
            return false;
        }

        if ($obPosition && !$this->checkPosition($obPosition)) {
            Log::info('SpecificPriceByQuantityDiscountPosition::check - Position check failed');
            return false;
        }

        // Use QuantityChecker to check if total quantity meets the limit
        $obPositionList = $obProcessor->getPositionList();
        if (empty($obPositionList) || (is_object($obPositionList) && method_exists($obPositionList, 'isEmpty') && $obPositionList->isEmpty())) {
            Log::info('SpecificPriceByQuantityDiscountPosition::check - Position list is empty');
            return false;
        }

        // Create a closure that has access to the protected checkPosition method
        $fnCheckPosition = function($obPositionItem) {
            return $this->checkPosition($obPositionItem);
        };

        $bResult = QuantityChecker::instance()->checkQuantityLimit($this, $obPositionList, $fnCheckPosition);
        Log::info('SpecificPriceByQuantityDiscountPosition::check - QuantityChecker result: ' . ($bResult ? 'true' : 'false'));
        
        return $bResult;
    }

    /**
     * Calculate item discount and add discount to price container
     * @param ItemPriceContainer $obPriceContainer
     * @param \Lovata\OrdersShopaholic\Classes\PromoMechanism\AbstractPromoMechanismProcessor $obProcessor
     * @param \Lovata\OrdersShopaholic\Classes\Item\CartPositionItem|\Lovata\OrdersShopaholic\Models\OrderPosition $obPosition
     * @return ItemPriceContainer
     */
    public function calculateItemDiscount($obPriceContainer, $obProcessor, $obPosition)
    {
        Log::info('SpecificPriceByQuantityDiscountPosition::calculateItemDiscount - Starting for position: ' . 
            (isset($obPosition->id) ? $obPosition->id : 'unknown'));
        
        $this->bApplied = false;
        if (!$this->check($obProcessor, $obPosition)) {
            Log::info('SpecificPriceByQuantityDiscountPosition::calculateItemDiscount - Check failed');
            return $obPriceContainer;
        }

        Log::info('SpecificPriceByQuantityDiscountPosition::calculateItemDiscount - Check passed, applying target price');
        
        $bPriceIncludeTax = TaxHelper::instance()->isPriceIncludeTax();
        $sFormulaCalculationDiscount = Settings::getValue('formula_calculate_discount_from_price', self::DISCOUNT_FROM_PRICE);
        $fTargetPrice = PriceHelper::toFloat($this->fDiscountValue);
        
        Log::info('SpecificPriceByQuantityDiscountPosition::calculateItemDiscount - Target price: ' . $fTargetPrice);

        // Get unit price list
        $arUnitPriceList = $obPriceContainer->getUnitPriceList();
        Log::info('SpecificPriceByQuantityDiscountPosition::calculateItemDiscount - Unit price list count: ' . count($arUnitPriceList));
        
        $bAnyPriceChanged = false;

        foreach ($arUnitPriceList as $iKey => &$fPrice) {
            Log::info('SpecificPriceByQuantityDiscountPosition::calculateItemDiscount - Unit ' . $iKey . ' original price: ' . $fPrice);
            
            // We'll only apply the target price if the current price is higher
            $fAdjustedPrice = $fPrice;
            
            // Prepare price, before applying discount
            if ($bPriceIncludeTax && $sFormulaCalculationDiscount == self::DISCOUNT_FROM_PRICE_WITHOUT_TAX) {
                $fAdjustedPrice = TaxHelper::instance()->calculatePriceWithoutTax($fAdjustedPrice, $obPriceContainer->tax_percent);
                Log::info('SpecificPriceByQuantityDiscountPosition::calculateItemDiscount - Converted to price without tax: ' . $fAdjustedPrice);
            } else if (!$bPriceIncludeTax && $sFormulaCalculationDiscount == self::DISCOUNT_FROM_PRICE_WITH_TAX) {
                $fAdjustedPrice = TaxHelper::instance()->calculatePriceWithTax($fAdjustedPrice, $obPriceContainer->tax_percent);
                Log::info('SpecificPriceByQuantityDiscountPosition::calculateItemDiscount - Converted to price with tax: ' . $fAdjustedPrice);
            }
            
            // Only apply the target price if current price is higher
            if ($fAdjustedPrice > $fTargetPrice) {
                Log::info('SpecificPriceByQuantityDiscountPosition::calculateItemDiscount - Applying target price: ' . $fTargetPrice);
                $fAdjustedPrice = $fTargetPrice;
                $bAnyPriceChanged = true;
            } else {
                Log::info('SpecificPriceByQuantityDiscountPosition::calculateItemDiscount - Current price is already below target, keeping: ' . $fAdjustedPrice);
            }

            // Calculate price after applying the discount
            if ($bPriceIncludeTax && $sFormulaCalculationDiscount == self::DISCOUNT_FROM_PRICE_WITHOUT_TAX) {
                $fAdjustedPrice = TaxHelper::instance()->calculatePriceWithTax($fAdjustedPrice, $obPriceContainer->tax_percent);
                Log::info('SpecificPriceByQuantityDiscountPosition::calculateItemDiscount - Converted back to price with tax: ' . $fAdjustedPrice);
            } else if (!$bPriceIncludeTax && $sFormulaCalculationDiscount == self::DISCOUNT_FROM_PRICE_WITH_TAX) {
                $fAdjustedPrice = TaxHelper::instance()->calculatePriceWithoutTax($fAdjustedPrice, $obPriceContainer->tax_percent);
                Log::info('SpecificPriceByQuantityDiscountPosition::calculateItemDiscount - Converted back to price without tax: ' . $fAdjustedPrice);
            }
            
            $fPrice = $fAdjustedPrice;
            Log::info('SpecificPriceByQuantityDiscountPosition::calculateItemDiscount - Final price for unit ' . $iKey . ': ' . $fPrice);
        }

        $this->bApplied = $bAnyPriceChanged;
        if ($bAnyPriceChanged) {
            Log::info('SpecificPriceByQuantityDiscountPosition::calculateItemDiscount - Discount was applied');
            $obPriceContainer->addDiscount($arUnitPriceList, $this);
        } else {
            Log::info('SpecificPriceByQuantityDiscountPosition::calculateItemDiscount - No price changes were needed');
        }

        return $obPriceContainer;
    }
    
    /**
     * Special handling for applyFixedDiscount to set a specific target price
     * @param float $fPrice Current price
     * @return float Price after discount applied
     */
    protected function applyFixedDiscount($fPrice)
    {
        if ($this->bIncrease) {
            // Keep the original behavior for increases
            $fPrice = PriceHelper::round($fPrice + $this->fDiscountValue);
        } else {
            // If the current price is higher than target price, set it to target price
            if ($fPrice > $this->fDiscountValue) {
                $fPrice = PriceHelper::round($this->fDiscountValue);
            }
            
            // Ensure price doesn't go below zero (safety check)
            if ($fPrice < 0) {
                $fPrice = 0;
            }
        }

        return $fPrice;
    }
}