<?php declare(strict_types=1);

/**
 * File path: plugins/logingrupa/extendpromomechanism/classes/promomechanism/bundleprice/BundlePriceDiscountPosition.php
 */

namespace Logingrupa\ExtendPromoMechanism\Classes\PromoMechanism\BundlePrice;

use ReflectionProperty;
use Lovata\OrdersShopaholic\Classes\Item\CartPositionItem;
use Lovata\OrdersShopaholic\Classes\PromoMechanism\AbstractPromoMechanism;
use Lovata\OrdersShopaholic\Classes\PromoMechanism\InterfacePromoMechanism;
use Lovata\OrdersShopaholic\Classes\PromoMechanism\ItemPriceContainer;
use Lovata\Shopaholic\Classes\Helper\CurrencyHelper;
use Lovata\Toolbox\Classes\Helper\PriceHelper;
use Logingrupa\ExtendPromoMechanism\Classes\PromoMechanism\BundleUnitPriceAllocator;

/**
 * Class BundlePriceDiscountPosition
 * Every full bundle of "quantity_limit" qualifying units, pooled across positions, costs "discount_value" in total.
 * @package Logingrupa\ExtendPromoMechanism\Classes\PromoMechanism\BundlePrice
 */
class BundlePriceDiscountPosition extends AbstractPromoMechanism implements InterfacePromoMechanism
{
    const LANG_NAME = 'logingrupa.extendpromomechanism::lang.promo_mechanism_type.bundle_price_discount_position';

    /** @var bool */
    protected $bPositionHasLeftoverUnit = false;

    /**
     * Get mechanism type (position|total_position|shipping|total)
     * @return string
     */
    public static function getType(): string
    {
        return self::TYPE_POSITION;
    }

    /**
     * A position with a unit left outside every full bundle stays open for lower priority mechanisms
     * @return bool
     */
    public function isFinal(): bool
    {
        return parent::isFinal() && !$this->bPositionHasLeftoverUnit;
    }

    /**
     * Check discount condition
     * @param \Lovata\OrdersShopaholic\Classes\PromoMechanism\AbstractPromoMechanismProcessor $obProcessor
     * @param \Lovata\OrdersShopaholic\Classes\Item\CartPositionItem|\Lovata\OrdersShopaholic\Models\OrderPosition|null $obPosition
     * @return bool
     */
    protected function check($obProcessor, $obPosition = null): bool
    {
        if (empty($obPosition) || !parent::check($obProcessor, $obPosition) || !$this->checkPosition($obPosition)) {
            return false;
        }

        return $this->getBundleSize() > 0;
    }

    /**
     * Set the bundle unit prices on the units of the position that fall inside a full bundle, never raising a price
     * @param ItemPriceContainer $obPriceContainer
     * @param \Lovata\OrdersShopaholic\Classes\PromoMechanism\AbstractPromoMechanismProcessor $obProcessor
     * @param \Lovata\OrdersShopaholic\Classes\Item\CartPositionItem|\Lovata\OrdersShopaholic\Models\OrderPosition $obPosition
     * @return ItemPriceContainer
     */
    public function calculateItemDiscount($obPriceContainer, $obProcessor, $obPosition): ItemPriceContainer
    {
        $this->bApplied = false;
        $this->bPositionHasLeftoverUnit = false;
        if (!$this->check($obProcessor, $obPosition)) {
            return $obPriceContainer;
        }

        // The bundle total is a shelf price, so the discount formula setting does not apply to it
        $iMinorUnitScale = $this->getMinorUnitScale();
        $iBundleTotalMinorUnits = (int) round(CurrencyHelper::instance()->convert($this->fDiscountValue) * $iMinorUnitScale);
        if ($iBundleTotalMinorUnits < 1) {
            return $obPriceContainer;
        }

        $arBundleUnitPriceList = BundleUnitPriceAllocator::instance()->getPositionUnitPriceList(
            $this->getQuantityByPositionID($obProcessor),
            (int) $obPosition->id,
            $this->getBundleSize(),
            $iBundleTotalMinorUnits,
            $iMinorUnitScale
        );
        $this->bPositionHasLeftoverUnit = count($arBundleUnitPriceList) < (int) $obPosition->quantity;

        $arUnitPriceList = $obPriceContainer->getUnitPriceList();
        foreach ($arBundleUnitPriceList as $iKey => $fBundleUnitPrice) {
            if ($arUnitPriceList[$iKey] <= $fBundleUnitPrice) {
                continue;
            }

            $arUnitPriceList[$iKey] = $fBundleUnitPrice;
            $this->bApplied = true;
        }

        if ($this->bApplied) {
            $obPriceContainer->addDiscount($arUnitPriceList, $this);
        }

        return $obPriceContainer;
    }

    /**
     * Get the quantity of every qualifying position, most expensive first, then by position ID.
     * The bundles take the dearest units whatever the order they were added in, and cart and order allocate the same units.
     * @param \Lovata\OrdersShopaholic\Classes\PromoMechanism\AbstractPromoMechanismProcessor $obProcessor
     * @return array
     */
    protected function getQuantityByPositionID($obProcessor): array
    {
        $arQualifyingPositionList = [];
        foreach ($obProcessor->getPositionList() as $obPositionItem) {
            if (!$this->checkPosition($obPositionItem)) {
                continue;
            }

            $arQualifyingPositionList[] = [
                'id'       => (int) $obPositionItem->id,
                'quantity' => (int) $obPositionItem->quantity,
                'price'    => $this->getUnitBasePrice($obPositionItem),
            ];
        }

        usort($arQualifyingPositionList, fn($arPrev, $arNext) => [$arNext['price'], $arPrev['id']] <=> [$arPrev['price'], $arNext['id']]);

        return array_column($arQualifyingPositionList, 'quantity', 'id');
    }

    /**
     * Get the unit price a position starts from, read the same way the cart and order processors build their price container
     * @param \Lovata\OrdersShopaholic\Classes\Item\CartPositionItem|\Lovata\OrdersShopaholic\Models\OrderPosition $obPosition
     * @return float
     */
    protected function getUnitBasePrice($obPosition): float
    {
        if ($obPosition instanceof CartPositionItem) {
            return (float) $obPosition->item->price_value;
        }

        return (float) $obPosition->price_value;
    }

    /**
     * Get the number of minor units in one major unit of the displayed prices
     * Whole-number currency shops (.no NOK) set PriceHelper decimals to 0 at runtime, see storeextender CurrencyHelperSwapper
     * @return int
     */
    protected function getMinorUnitScale(): int
    {
        $obDecimalProperty = new ReflectionProperty(PriceHelper::class, 'iDecimal');

        return 10 ** (int) $obDecimalProperty->getValue(PriceHelper::instance());
    }

    /**
     * Get the number of units in one bundle
     * @return int
     */
    protected function getBundleSize(): int
    {
        return (int) $this->getProperty('quantity_limit');
    }
}
