<?php declare(strict_types=1);

/**
 * File path: plugins/lovata/extendpromomechanism/classes/event/ExtendPromoMechanismHandler.php
 */

namespace Logingrupa\ExtendPromoMechanism\Classes\Event;

use Logingrupa\ExtendPromoMechanism\Classes\PromoMechanism\SpecificPriceByQuantity\SpecificPriceByQuantityDiscountPosition;
use Lovata\OrdersShopaholic\Classes\PromoMechanism\PromoMechanismStore;
use October\Rain\Events\Dispatcher;
use Log;

/**
 * Class ExtendPromoMechanismHandler
 * @package Lovata\ExtendPromoMechanism\Classes\Event
 */
class ExtendPromoMechanismHandler
{
    /**
     * Subscribe to events
     * @param Dispatcher $obEvent
     * @return void
     */
    public function subscribe(Dispatcher $obEvent): void
    {
        Log::info('ExtendPromoMechanismHandler::subscribe - Registering promo mechanism');
        
        $obEvent->listen(PromoMechanismStore::EVENT_ADD_PROMO_MECHANISM_CLASS, function () {
            Log::info('ExtendPromoMechanismHandler - Adding SpecificPriceByQuantityDiscountPosition to promo mechanism list');
            return [
                SpecificPriceByQuantityDiscountPosition::class,
            ];
        });
    }
}