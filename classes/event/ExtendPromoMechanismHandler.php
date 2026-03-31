<?php declare(strict_types=1);

/**
 * File path: plugins/logingrupa/extendpromomechanism/classes/event/ExtendPromoMechanismHandler.php
 */

namespace Logingrupa\ExtendPromoMechanism\Classes\Event;

use Logingrupa\ExtendPromoMechanism\Classes\PromoMechanism\SpecificPriceByQuantity\SpecificPriceByQuantityDiscountPosition;
use Lovata\OrdersShopaholic\Classes\PromoMechanism\PromoMechanismStore;
use Log;

/**
 * Class ExtendPromoMechanismHandler
 * @package Lovata\ExtendPromoMechanism\Classes\Event
 */
class ExtendPromoMechanismHandler
{
    /**
     * Subscribe to events
     * @param mixed $obEvent
     * @return void
     */
    public function subscribe($obEvent): void
    {
        //Log::info('ExtendPromoMechanismHandler::subscribe - Registering promo mechanism');
        
        $obEvent->listen(PromoMechanismStore::EVENT_ADD_PROMO_MECHANISM_CLASS, function () {
            //Log::info('ExtendPromoMechanismHandler - Adding SpecificPriceByQuantityDiscountPosition to promo mechanism list');
            return [
                SpecificPriceByQuantityDiscountPosition::class,
            ];
        });
    }
}