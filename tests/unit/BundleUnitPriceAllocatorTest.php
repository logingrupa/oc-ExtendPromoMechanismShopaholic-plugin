<?php

use Logingrupa\ExtendPromoMechanism\Classes\PromoMechanism\BundleUnitPriceAllocator;

function allocateBundleUnits(array $arQuantityByPositionID, int $iPositionID, int $iBundleSize, int $iBundleTotalMinorUnits, int $iMinorUnitScale = 100): array
{
    return BundleUnitPriceAllocator::instance()->getPositionUnitPriceList($arQuantityByPositionID, $iPositionID, $iBundleSize, $iBundleTotalMinorUnits, $iMinorUnitScale);
}

test('a single unit waits for its pair', function () {
    expect(allocateBundleUnits([1 => 1], 1, 2, 3500))->toBe([]);
});

test('two units of one line form one bundle', function () {
    expect(allocateBundleUnits([1 => 2], 1, 2, 3500))->toBe([17.5, 17.5]);
});

test('a bundle total that divides evenly still gives float prices', function () {
    expect(allocateBundleUnits([1 => 2], 1, 2, 3600))->toBe([18.0, 18.0]);
});

test('units of different lines pair with each other', function () {
    $arQuantityByPositionID = [1 => 1, 2 => 1];

    expect(allocateBundleUnits($arQuantityByPositionID, 1, 2, 3500))->toBe([17.5])
        ->and(allocateBundleUnits($arQuantityByPositionID, 2, 2, 3500))->toBe([17.5]);
});

test('the unit left over after the last full bundle is on the last listed line', function () {
    $arQuantityByPositionID = [1 => 1, 2 => 1, 3 => 1];

    expect(allocateBundleUnits($arQuantityByPositionID, 1, 2, 3500))->toBe([17.5])
        ->and(allocateBundleUnits($arQuantityByPositionID, 2, 2, 3500))->toBe([17.5])
        ->and(allocateBundleUnits($arQuantityByPositionID, 3, 2, 3500))->toBe([]);
});

test('a line of three holds one bundle and one leftover unit', function () {
    expect(allocateBundleUnits([1 => 3], 1, 2, 3500))->toBe([17.5, 17.5]);
});

test('a bundle can span a line boundary', function () {
    $arQuantityByPositionID = [1 => 1, 2 => 2, 3 => 2];

    expect(allocateBundleUnits($arQuantityByPositionID, 1, 2, 3500))->toBe([17.5])
        ->and(allocateBundleUnits($arQuantityByPositionID, 2, 2, 3500))->toBe([17.5, 17.5])
        ->and(allocateBundleUnits($arQuantityByPositionID, 3, 2, 3500))->toBe([17.5]);
});

test('three for 35.00 splits the cents so the bundle sums exactly', function () {
    $arUnitPriceList = allocateBundleUnits([1 => 3], 1, 3, 3500);

    expect($arUnitPriceList)->toBe([11.67, 11.67, 11.66])
        ->and(round(array_sum($arUnitPriceList), 2))->toBe(35.0);
});

test('the cent split follows the bundle slot across lines', function () {
    $arQuantityByPositionID = [1 => 1, 2 => 1, 3 => 1, 4 => 3];

    expect(allocateBundleUnits($arQuantityByPositionID, 1, 3, 3500))->toBe([11.67])
        ->and(allocateBundleUnits($arQuantityByPositionID, 2, 3, 3500))->toBe([11.67])
        ->and(allocateBundleUnits($arQuantityByPositionID, 3, 3, 3500))->toBe([11.66])
        ->and(allocateBundleUnits($arQuantityByPositionID, 4, 3, 3500))->toBe([11.67, 11.67, 11.66]);
});

test('a whole-number currency splits in whole units', function () {
    expect(allocateBundleUnits([1 => 2], 1, 2, 399, 1))->toBe([200.0, 199.0])
        ->and(allocateBundleUnits([1 => 3], 1, 3, 400, 1))->toBe([134.0, 133.0, 133.0]);
});

test('every full bundle sums to the total for any quantity', function (int $iQuantity, int $iBundleSize, int $iBundleTotalMinorUnits) {
    $arUnitPriceList = allocateBundleUnits([1 => $iQuantity], 1, $iBundleSize, $iBundleTotalMinorUnits);
    $iBundleCount = intdiv($iQuantity, $iBundleSize);

    expect(count($arUnitPriceList))->toBe($iBundleCount * $iBundleSize)
        ->and((int) round(array_sum($arUnitPriceList) * 100))->toBe($iBundleTotalMinorUnits * $iBundleCount);
})->with([
    [7, 2, 3500],
    [10, 3, 3500],
    [9, 3, 3501],
    [5, 2, 3600],
    [4, 4, 3],
]);

test('invalid input fails loudly', function (array $arQuantityByPositionID, int $iPositionID, int $iBundleSize, int $iBundleTotalMinorUnits, int $iMinorUnitScale) {
    allocateBundleUnits($arQuantityByPositionID, $iPositionID, $iBundleSize, $iBundleTotalMinorUnits, $iMinorUnitScale);
})->with([
    'bundle size zero' => [[1 => 2], 1, 0, 3500, 100],
    'bundle total zero' => [[1 => 2], 1, 2, 0, 100],
    'minor unit scale zero' => [[1 => 2], 1, 2, 3500, 0],
    'position not listed' => [[1 => 2], 9, 2, 3500, 100],
])->throws(InvalidArgumentException::class);
