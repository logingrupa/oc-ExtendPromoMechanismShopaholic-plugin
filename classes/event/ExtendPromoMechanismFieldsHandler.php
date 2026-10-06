<?php declare(strict_types=1);

/**
 * File path: plugins/logingrupa/extendpromomechanism/classes/event/ExtendPromoMechanismFieldsHandler.php
 */

namespace Logingrupa\ExtendPromoMechanism\Classes\Event;

use Arr;
use Lovata\OrdersShopaholic\Controllers\PromoMechanisms;
use Lovata\OrdersShopaholic\Models\PromoMechanism;
use Lovata\Toolbox\Classes\Event\AbstractBackendFieldHandler;
use Logingrupa\ExtendPromoMechanism\Classes\PromoMechanism\BundlePrice\BundlePriceDiscountPosition;
use Logingrupa\ExtendPromoMechanism\Classes\PromoMechanism\SpecificPriceByQuantity\SpecificPriceByQuantityDiscountPosition;
use Log;

/**
 * Class ExtendPromoMechanismFieldsHandler
 * @package Logingrupa\ExtendPromoMechanism\Classes\Event
 */
class ExtendPromoMechanismFieldsHandler extends AbstractBackendFieldHandler
{
    /**
     * Extend form fields
     * @param \Backend\Widgets\Form $obWidget
     */
    protected function extendFields($obWidget): void
    {
        //Log::info('ExtendPromoMechanismFieldsHandler::extendFields - Starting field extension');
        
        // Get the configuration for the quantity_limit field
        $arConfigQuantityLimit = optional($obWidget->getField('property[quantity_limit]'))->config;
        
        if (!empty($arConfigQuantityLimit)) {
            // Extend the trigger condition to include our mechanism
            $sCondition = trim((string) Arr::get($arConfigQuantityLimit, 'trigger.condition'));
            $sConditionExtended = $sCondition
                . ' || value[' . SpecificPriceByQuantityDiscountPosition::class . ']'
                . ' || value[' . BundlePriceDiscountPosition::class . ']';
            Arr::set($arConfigQuantityLimit, 'trigger.condition', $sConditionExtended);

            Arr::set($arConfigQuantityLimit, 'label', 'Quantity (minimum total quantity, or units in one bundle)');
            Arr::set($arConfigQuantityLimit, 'comment', 'Set exact price: the target price applies only when the cart holds at least this many qualifying items in total, for example "20". Bundle price: the number of units in one bundle, for example "2" for "2 pcs for 35 EUR".');
            
            //Log::info('ExtendPromoMechanismFieldsHandler::extendFields - Extended quantity_limit trigger condition: ' . $sConditionExtended);
            
            // Update the field with modified configuration
            $obWidget->addFields([
                'property[quantity_limit]' => $arConfigQuantityLimit,
            ]);
        }

        // Add informational field
        $obWidget->addFields([
            'property[target_price_info]' => [
                'label' => 'Important Note',
                'type' => 'partial',
                'path' => '$/logingrupa/extendpromomechanism/partials/_target_price_info.htm',
                'span' => 'full',
                'trigger' => [
                    'action' => 'show',
                    'field' => 'type',
                    'condition' => 'value[' . SpecificPriceByQuantityDiscountPosition::class . ']',
                ],
            ],
            'property[bundle_price_info]' => [
                'label' => 'Important Note',
                'type' => 'partial',
                'path' => '$/logingrupa/extendpromomechanism/partials/_bundle_price_info.htm',
                'span' => 'full',
                'trigger' => [
                    'action' => 'show',
                    'field' => 'type',
                    'condition' => 'value[' . BundlePriceDiscountPosition::class . ']',
                ],
            ],
        ]);
        
        //Log::info('ExtendPromoMechanismFieldsHandler::extendFields - Added target_price_info field');
        
        // Modify the discount_value field
        $discountValueField = $obWidget->getField('discount_value');
        if (!empty($discountValueField)) {
            $discountValueConfig = $discountValueField->config;
            
            // Update label and comment to make it clear this is the target price
            if (isset($discountValueConfig['label'])) {
                $discountValueConfig['label'] = 'Target price (per item, or for the whole bundle)';
            }

            $discountValueConfig['comment'] = 'Set exact price: the price of each qualifying item. Bundle price: the price of the whole bundle, for example "35.00".';
            
            // Update commentAttributes property to change comment based on selected type
            $commentAttributes = Arr::get($discountValueConfig, 'commentAttributes', []);
            $commentAttributes['data-mechanism-' . str_replace('\\', '-', SpecificPriceByQuantityDiscountPosition::class)] = 'logingrupa.extendpromomechanism::lang.field.target_price';
            
            Arr::set($discountValueConfig, 'commentAttributes', $commentAttributes);
            
            //Log::info('ExtendPromoMechanismFieldsHandler::extendFields - Modified discount_value field comment');
            
            // Apply changes to the field
            $obWidget->addFields([
                'discount_value' => $discountValueConfig,
            ]);
        }
    }

    /**
     * Get model class
     * @return string
     */
    protected function getModelClass(): string
    {
        return PromoMechanism::class;
    }

    /**
     * Get controller class
     * @return string
     */
    protected function getControllerClass(): string
    {
        return PromoMechanisms::class;
    }
}