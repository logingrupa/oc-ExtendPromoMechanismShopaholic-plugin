<?php declare(strict_types=1);

/**
 * File path: plugins/logingrupa/extendpromomechanism/classes/event/ExtendPromoMechanismFieldsHandler.php
 */

namespace Logingrupa\ExtendPromoMechanism\Classes\Event;

use Arr;
use Lovata\OrdersShopaholic\Controllers\PromoMechanisms;
use Lovata\OrdersShopaholic\Models\PromoMechanism;
use Lovata\Toolbox\Classes\Event\AbstractBackendFieldHandler;
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
        Log::info('ExtendPromoMechanismFieldsHandler::extendFields - Starting field extension');
        
        // Get the configuration for the quantity_limit field
        $arConfigQuantityLimit = optional($obWidget->getField('property[quantity_limit]'))->config;
        
        if (!empty($arConfigQuantityLimit)) {
            // Extend the trigger condition to include our mechanism
            $sCondition = trim((string) Arr::get($arConfigQuantityLimit, 'trigger.condition'));
            $sConditionExtended = $sCondition . ' || value[' . SpecificPriceByQuantityDiscountPosition::class . ']';
            Arr::set($arConfigQuantityLimit, 'trigger.condition', $sConditionExtended);
            
            // Update the label and comment to make it clear this is for the minimum quantity threshold
            Arr::set($arConfigQuantityLimit, 'label', 'Minimum total quantity to trigger discount');
            Arr::set($arConfigQuantityLimit, 'comment', 'The target price will ONLY be applied when the total quantity of items in the cart is greater than or equal to this value. For example, enter "20" to apply the target price when a customer has 20 or more items.');
            
            Log::info('ExtendPromoMechanismFieldsHandler::extendFields - Extended quantity_limit trigger condition: ' . $sConditionExtended);
            
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
        ]);
        
        Log::info('ExtendPromoMechanismFieldsHandler::extendFields - Added target_price_info field');
        
        // Modify the discount_value field
        $discountValueField = $obWidget->getField('discount_value');
        if (!empty($discountValueField)) {
            $discountValueConfig = $discountValueField->config;
            
            // Update label and comment to make it clear this is the target price
            if (isset($discountValueConfig['label'])) {
                $discountValueConfig['label'] = 'Target price per item';
            }
            
            $discountValueConfig['comment'] = 'Enter the exact price you want each qualifying item to cost';
            
            // Update commentAttributes property to change comment based on selected type
            $commentAttributes = Arr::get($discountValueConfig, 'commentAttributes', []);
            $commentAttributes['data-mechanism-' . str_replace('\\', '-', SpecificPriceByQuantityDiscountPosition::class)] = 'logingrupa.extendpromomechanism::lang.field.target_price';
            
            Arr::set($discountValueConfig, 'commentAttributes', $commentAttributes);
            
            Log::info('ExtendPromoMechanismFieldsHandler::extendFields - Modified discount_value field comment');
            
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