<?php declare(strict_types=1);

/**
 * File path: plugins/logingrupa/extendpromomechanism/Plugin.php
 */

namespace Logingrupa\ExtendPromoMechanism;

use Log;
use Event;
use System\Classes\PluginBase;
use Logingrupa\ExtendPromoMechanism\Classes\Event\ExtendPromoMechanismHandler;
use Logingrupa\ExtendPromoMechanism\Classes\Event\ExtendPromoMechanismFieldsHandler;

/**
 * ExtendPromoMechanism Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * Required plugins
     * @var array
     */
    public $require = [
        'Lovata.OrdersShopaholic',
        'Lovata.Shopaholic',
    ];

    /**
     * Returns information about this plugin
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'lovata.extendpromomechanism::lang.plugin.name',
            'description' => 'lovata.extendpromomechanism::lang.plugin.description',
            'author'      => 'Logingrupa',
            'icon'        => 'icon-percent',
        ];
    }

    /**
     * Register method, called when the plugin is first registered
     * @return void
     */
    public function register()
    {
        //Log::info('ExtendPromoMechanism::register - Plugin registered');
    }

    /**
     * Boot method, called right before the request route
     * @return void
     */
    public function boot()
    {
        //Log::info('ExtendPromoMechanism::boot - Plugin booting');
        
        // Register event handlers
        Event::subscribe(new ExtendPromoMechanismHandler());
        Event::subscribe(new ExtendPromoMechanismFieldsHandler());
        
        //Log::info('ExtendPromoMechanism::boot - Event handlers registered');
    }
}