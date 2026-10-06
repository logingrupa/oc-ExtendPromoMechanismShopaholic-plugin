<?php

use Logingrupa\ExtendPromoMechanism\Classes\PromoMechanism\BundlePrice\BundlePriceDiscountPosition;
use Lovata\OrdersShopaholic\Classes\PromoMechanism\ItemPriceContainer;
use Lovata\Shopaholic\Classes\Helper\CurrencyHelper;
use Lovata\Toolbox\Classes\Helper\PriceHelper;

beforeEach(function () {
    $obCurrencyHelper = Mockery::mock(CurrencyHelper::class);
    $obCurrencyHelper->shouldReceive('convert')->andReturnUsing(fn ($fPrice) => $fPrice);
    setSingletonInstance(CurrencyHelper::class, $obCurrencyHelper);
    setPriceHelperDecimals(2);
});

afterEach(function () {
    CurrencyHelper::forgetInstance();
    PriceHelper::forgetInstance();
});

function setSingletonInstance(string $sClass, object $obInstance): void
{
    (new ReflectionProperty($sClass, 'instance'))->setValue(null, $obInstance);
}

function setPriceHelperDecimals(int $iDecimal): void
{
    $obPriceHelper = (new ReflectionClass(PriceHelper::class))->newInstanceWithoutConstructor();
    (new ReflectionProperty(PriceHelper::class, 'iDecimal'))->setValue($obPriceHelper, $iDecimal);
    setSingletonInstance(PriceHelper::class, $obPriceHelper);
}

function makeBundlePosition(int $iPositionID, int $iQuantity, float $fPrice = 20.88, int $iProductID = 428): object
{
    return (object) ['id' => $iPositionID, 'quantity' => $iQuantity, 'price_value' => $fPrice, 'offer' => (object) ['product_id' => $iProductID]];
}

function makeBundleProcessor(array $arPositionList): object
{
    return new class ($arPositionList) {
        public function __construct(private array $arPositionList)
        {
        }

        public function getPositionList(): array
        {
            return $this->arPositionList;
        }

        public function getShippingType()
        {
            return null;
        }

        public function getPaymentMethod()
        {
            return null;
        }
    };
}

function makeBundleMechanism(float $fBundleTotal, int $iBundleSize, bool $bIsFinal = false): BundlePriceDiscountPosition
{
    $obMechanism = new BundlePriceDiscountPosition(260, $fBundleTotal, 'fixed', $bIsFinal, ['quantity_limit' => $iBundleSize], false);
    $obMechanism->setCheckPositionCallback(fn ($obPosition) => !empty($obPosition) && $obPosition->offer->product_id === 428);

    return $obMechanism;
}

/**
 * Price every position of the list through the mechanism, the way the cart and order processors do
 */
function priceBundleLines(BundlePriceDiscountPosition $obMechanism, array $arPositionList): array
{
    $obProcessor = makeBundleProcessor($arPositionList);
    $arLineTotal = [];
    foreach ($arPositionList as $obPosition) {
        $obPriceContainer = new ItemPriceContainer($obPosition->price_value, $obPosition->price_value, 21, $obPosition->quantity);
        $arLineTotal[$obPosition->id] = round($obMechanism->calculateItemDiscount($obPriceContainer, $obProcessor, $obPosition)->price_value, 2);
    }

    return $arLineTotal;
}

function makeBundleLines(array $arQuantityList): array
{
    $arPositionList = [];
    foreach ($arQuantityList as $iIndex => $iQuantity) {
        $arPositionList[] = makeBundlePosition(101 + $iIndex, $iQuantity);
    }

    return $arPositionList;
}

test('owner scenarios for 2 pcs for 35.00 at a normal price of 20.88', function (array $arQuantityList, float $fExpectedTotal) {
    $arLineTotal = priceBundleLines(makeBundleMechanism(35.0, 2), makeBundleLines($arQuantityList));

    expect(round(array_sum($arLineTotal), 2))->toBe($fExpectedTotal);
})->with([
    'one unit' => [[1], 20.88],
    'one line of two' => [[2], 35.0],
    'two colours, one each' => [[1, 1], 35.0],
    'three colours, one each' => [[1, 1, 1], 55.88],
    'one line of three' => [[3], 55.88],
    'four units over three lines' => [[2, 1, 1], 70.0],
    'five units' => [[2, 2, 1], 90.88],
]);

test('the leftover unit stays on the last added line', function () {
    $arLineTotal = priceBundleLines(makeBundleMechanism(35.0, 2), makeBundleLines([1, 1, 1]));

    expect($arLineTotal)->toBe([101 => 17.5, 102 => 17.5, 103 => 20.88]);
});

test('allocation follows position id, not list order, so cart and order agree', function () {
    $arLineTotal = priceBundleLines(makeBundleMechanism(35.0, 2), [
        makeBundlePosition(103, 1),
        makeBundlePosition(101, 1),
        makeBundlePosition(102, 1),
    ]);

    expect($arLineTotal)->toBe([103 => 20.88, 101 => 17.5, 102 => 17.5]);
});

test('the dearest units fill the bundles whatever the order they were added in', function (array $arPositionList, float $fExpectedTotal) {
    $arLineTotal = priceBundleLines(makeBundleMechanism(35.0, 3), $arPositionList);

    expect(round(array_sum($arLineTotal), 2))->toBe($fExpectedTotal);
})->with([
    'cheap first, dear last' => [[makeBundlePosition(101, 1, 16.9), makeBundlePosition(102, 1, 16.9), makeBundlePosition(103, 1, 16.9), makeBundlePosition(104, 1, 29.5)], 51.9],
    'dear first' => [[makeBundlePosition(101, 1, 29.5), makeBundlePosition(102, 1, 16.9), makeBundlePosition(103, 1, 16.9), makeBundlePosition(104, 1, 16.9)], 51.9],
    'a cheap unit added second' => [[makeBundlePosition(101, 1, 16.9), makeBundlePosition(102, 1, 6.5), makeBundlePosition(103, 1, 16.9), makeBundlePosition(104, 1, 16.9)], 41.5],
]);

test('units outside the campaign scope neither count nor change', function () {
    $arLineTotal = priceBundleLines(makeBundleMechanism(35.0, 2), [
        makeBundlePosition(101, 1),
        makeBundlePosition(102, 1, 20.88, 999),
    ]);

    expect($arLineTotal)->toBe([101 => 20.88, 102 => 20.88]);
});

test('three for 35.00 charges exactly 35.00', function () {
    $arLineTotal = priceBundleLines(makeBundleMechanism(35.0, 3), [
        makeBundlePosition(101, 1, 16.9),
        makeBundlePosition(102, 1, 16.9),
        makeBundlePosition(103, 1, 16.9),
    ]);

    expect($arLineTotal)->toBe([101 => 11.67, 102 => 11.67, 103 => 11.66])
        ->and(round(array_sum($arLineTotal), 2))->toBe(35.0);
});

test('a unit already cheaper than its bundle share keeps its price', function () {
    $obMechanism = makeBundleMechanism(35.0, 2);
    $arLineTotal = priceBundleLines($obMechanism, [makeBundlePosition(101, 2, 15.68)]);

    expect($arLineTotal)->toBe([101 => 31.36])
        ->and($obMechanism->isApplied())->toBeFalse();
});

test('a final bundle mechanism stays open for a line that keeps a leftover unit', function () {
    $obMechanism = makeBundleMechanism(35.0, 2, true);

    priceBundleLines($obMechanism, [makeBundlePosition(101, 3)]);
    $bFinalWithLeftover = $obMechanism->isFinal();
    priceBundleLines($obMechanism, [makeBundlePosition(101, 2)]);

    expect($bFinalWithLeftover)->toBeFalse()
        ->and($obMechanism->isFinal())->toBeTrue();
});

test('a whole-number currency shop splits the bundle in whole units', function () {
    setPriceHelperDecimals(0);
    $arLineTotal = priceBundleLines(makeBundleMechanism(399.0, 2), [
        makeBundlePosition(101, 1, 249.0),
        makeBundlePosition(102, 1, 249.0),
    ]);

    expect($arLineTotal)->toBe([101 => 200.0, 102 => 199.0]);
});

test('a bundle total that rounds to nothing leaves every price alone', function () {
    setPriceHelperDecimals(0);
    $obMechanism = makeBundleMechanism(0.4, 2);
    $arLineTotal = priceBundleLines($obMechanism, [makeBundlePosition(101, 2, 249.0)]);

    expect($arLineTotal)->toBe([101 => 498.0])
        ->and($obMechanism->isApplied())->toBeFalse();
});

test('an empty bundle size leaves every price alone', function () {
    $obMechanism = makeBundleMechanism(35.0, 0);
    $arLineTotal = priceBundleLines($obMechanism, [makeBundlePosition(101, 2)]);

    expect($arLineTotal)->toBe([101 => 41.76])
        ->and($obMechanism->isApplied())->toBeFalse();
});
