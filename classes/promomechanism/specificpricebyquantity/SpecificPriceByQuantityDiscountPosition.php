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
        if (!parent::check($obProcessor, $obPosition) || 
            ($obPosition && !$this->checkPosition($obPosition))) {
            return false;
        }

        $obPositionList = $obProcessor->getPositionList();
        if (empty($obPositionList) || (is_object($obPositionList) && 
            method_exists($obPositionList, 'isEmpty') && $obPositionList->isEmpty())) {
            return false;
        }

        return QuantityChecker::instance()->checkQuantityLimit(
            $this, 
            $obPositionList, 
            fn($obPositionItem) => $this->checkPosition($obPositionItem)
        );
    }

    /**
     * Calculate item discount and add discount to price container
     * @param ItemPriceContainer $obPriceContainer
     * @param \Lovata\OrdersShopaholic\Classes\PromoMechanism\AbstractPromoMechanismProcessor $obProcessor
     * @param \Lovata\OrdersShopaholic\Classes\Item\CartPositionItem|\Lovata\OrdersShopaholic\Models\OrderPosition $obPosition
     * @return ItemPriceContainer
     */
    public function calculateItemDiscount($obPriceContainer, $obProcessor, $obPosition): ItemPriceContainer
    {
        $this->bApplied = false;
        if (!$this->check($obProcessor, $obPosition)) {
            return $obPriceContainer;
        }
        
        $bPriceIncludeTax = TaxHelper::instance()->isPriceIncludeTax();
        $sFormulaCalculationDiscount = Settings::getValue('formula_calculate_discount_from_price', self::DISCOUNT_FROM_PRICE);
        
        // The discount_value represents our target price per item
        $fTargetPrice = $this->fDiscountValue;
        
        // Get unit price list
        $arUnitPriceList = $obPriceContainer->getUnitPriceList();
        $bAnyPriceChanged = false;

        foreach ($arUnitPriceList as $iKey => &$fPrice) {
            // Prepare price, before applying discount
            $fAdjustedPrice = $this->adjustPriceForTax(
                $fPrice, 
                $obPriceContainer->tax_percent, 
                $sFormulaCalculationDiscount, 
                $bPriceIncludeTax
            );
            
            // Only apply the target price if current price is higher than target
            if ($fAdjustedPrice > $fTargetPrice) {
                $fAdjustedPrice = $fTargetPrice;
                $bAnyPriceChanged = true;
            }

            // Convert back to original tax state
            if ($bPriceIncludeTax && $sFormulaCalculationDiscount == self::DISCOUNT_FROM_PRICE_WITHOUT_TAX) {
                $fAdjustedPrice = TaxHelper::instance()->calculatePriceWithTax($fAdjustedPrice, $obPriceContainer->tax_percent);
            } else if (!$bPriceIncludeTax && $sFormulaCalculationDiscount == self::DISCOUNT_FROM_PRICE_WITH_TAX) {
                $fAdjustedPrice = TaxHelper::instance()->calculatePriceWithoutTax($fAdjustedPrice, $obPriceContainer->tax_percent);
            }
            
            $fPrice = $fAdjustedPrice;
        }

        $this->bApplied = $bAnyPriceChanged;
        if ($bAnyPriceChanged) {
            $obPriceContainer->addDiscount($arUnitPriceList, $this);
        }

        return $obPriceContainer;
    }

    /**
     * Adjust price based on tax configuration
     * 
     * @param float $fPrice Price to adjust
     * @param float $fTaxPercent Tax percentage
     * @param string $sFormula Formula for tax calculation
     * @param bool $bIncludeTax Whether price includes tax
     * @return float Adjusted price
     */
    private function adjustPriceForTax(float $fPrice, float $fTaxPercent, string $sFormula, bool $bIncludeTax): float
    {
        if ($bIncludeTax && $sFormula == self::DISCOUNT_FROM_PRICE_WITHOUT_TAX) {
            return TaxHelper::instance()->calculatePriceWithoutTax($fPrice, $fTaxPercent);
        } 
        
        if (!$bIncludeTax && $sFormula == self::DISCOUNT_FROM_PRICE_WITH_TAX) {
            return TaxHelper::instance()->calculatePriceWithTax($fPrice, $fTaxPercent);
        }
        
        return $fPrice;
    }
    

}