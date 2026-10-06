<?php declare(strict_types=1);

/**
 * File path: plugins/logingrupa/extendpromomechanism/classes/promomechanism/BundleUnitPriceAllocator.php
 */

namespace Logingrupa\ExtendPromoMechanism\Classes\PromoMechanism;

use InvalidArgumentException;
use October\Rain\Support\Traits\Singleton;

/**
 * Class BundleUnitPriceAllocator
 * @package Logingrupa\ExtendPromoMechanism\Classes\PromoMechanism
 */
class BundleUnitPriceAllocator
{
    use Singleton;

    /**
     * Get the bundle price of every unit of one position that falls inside a full bundle.
     * Units are pooled across positions in list order; leftover units after the last full bundle get no price.
     * The bundle total is split in minor units, earlier slots of a bundle carry the remainder (3500 / 3 = 1167, 1167, 1166).
     *
     * @param array $arQuantityByPositionID position ID => quantity, in allocation order
     * @param int   $iPositionID
     * @param int   $iBundleSize
     * @param int   $iBundleTotalMinorUnits bundle total in minor units, 3500 for 35.00 EUR
     * @param int   $iMinorUnitScale minor units per major unit, 100 for cents, 1 for whole-number currencies
     * @return array list of unit prices for the bundled units of the position
     */
    public function getPositionUnitPriceList(array $arQuantityByPositionID, int $iPositionID, int $iBundleSize, int $iBundleTotalMinorUnits, int $iMinorUnitScale): array
    {
        if ($iBundleSize < 1) {
            throw new InvalidArgumentException("Bundle size must be at least 1, got: {$iBundleSize}");
        }
        if ($iBundleTotalMinorUnits < 1) {
            throw new InvalidArgumentException("Bundle total must be at least one minor unit, got: {$iBundleTotalMinorUnits}");
        }
        if ($iMinorUnitScale < 1) {
            throw new InvalidArgumentException("Minor unit scale must be at least 1, got: {$iMinorUnitScale}");
        }
        if (!array_key_exists($iPositionID, $arQuantityByPositionID)) {
            throw new InvalidArgumentException("Position {$iPositionID} is not in the qualifying position list");
        }

        $iUnitCountBefore = $this->getUnitCountBefore($arQuantityByPositionID, $iPositionID);
        $iBundledUnitCount = intdiv(array_sum($arQuantityByPositionID), $iBundleSize) * $iBundleSize;
        $iLastBundledUnitIndex = min($iUnitCountBefore + $arQuantityByPositionID[$iPositionID], $iBundledUnitCount);

        $arUnitPriceList = [];
        for ($iUnitIndex = $iUnitCountBefore; $iUnitIndex < $iLastBundledUnitIndex; $iUnitIndex++) {
            $arUnitPriceList[] = (float) ($this->getSlotMinorUnits($iBundleTotalMinorUnits, $iBundleSize, $iUnitIndex % $iBundleSize) / $iMinorUnitScale);
        }

        return $arUnitPriceList;
    }

    /**
     * Count the units of all positions listed before the given position
     * @param array $arQuantityByPositionID
     * @param int   $iPositionID
     * @return int
     */
    protected function getUnitCountBefore(array $arQuantityByPositionID, int $iPositionID): int
    {
        $iUnitCountBefore = 0;
        foreach ($arQuantityByPositionID as $iListPositionID => $iQuantity) {
            if ($iListPositionID === $iPositionID) {
                return $iUnitCountBefore;
            }

            $iUnitCountBefore += $iQuantity;
        }

        return $iUnitCountBefore;
    }

    /**
     * Get the price in minor units of one slot of a bundle
     * @param int $iBundleTotalMinorUnits
     * @param int $iBundleSize
     * @param int $iSlotIndex
     * @return int
     */
    protected function getSlotMinorUnits(int $iBundleTotalMinorUnits, int $iBundleSize, int $iSlotIndex): int
    {
        $iRemainderMinorUnits = $iBundleTotalMinorUnits % $iBundleSize;

        return intdiv($iBundleTotalMinorUnits, $iBundleSize) + ($iSlotIndex < $iRemainderMinorUnits ? 1 : 0);
    }
}
