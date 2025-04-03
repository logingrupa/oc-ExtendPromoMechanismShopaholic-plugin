# ExtendPromoMechanism Plugin for Shopaholic

A powerful extension for OctoberCMS Shopaholic that adds a dynamic pricing mechanism to set specific target prices when customers have a minimum quantity of items in their cart.

## Overview

This plugin enhances the Lovata Shopaholic e-commerce platform by adding a new promotional mechanism:

**"Set exact price when total quantity ≥ limit"** - This mechanism allows you to set an exact target price for products when a customer's cart reaches a specified quantity threshold.

Unlike standard percentage or fixed-amount discounts, this mechanism provides a way to dynamically price items to reach a specific price point when quantity conditions are met.

## Features

- **Target Price Mechanism**: Set an exact price for products when customers have a minimum quantity in cart
- **Quantity-Based Triggers**: Define the minimum total quantity of items required to activate the discount
- **Smart Price Logic**: Only applies to items where the original price is higher than the target price
- **Fully Integrated**: Works with Shopaholic's tax system and other features
- **Compatible with Price Types**: Works with all product price types in Shopaholic
- **Admin UI Integration**: Clear, intuitive interface for configuring promotions

## Use Cases

- **Bulk Pricing**: Automatically set all items to a specific price when customers buy in bulk
- **Volume Incentives**: Encourage customers to add more items to reach attractive price points
- **Simplified Promotions**: Easy way to implement "Everything for $X when you buy Y or more!"
- **Dynamic Pricing**: Create sophisticated pricing strategies based on cart quantity

## Requirements

- OctoberCMS
- Lovata.Shopaholic plugin
- Lovata.OrdersShopaholic plugin

## Installation

1. **Via Composer**
   ```bash
   php artisan plugin:install Logingrupa.ExtendPromoMechanism --from=git@github.com:logingrupa/oc-ExtendPromoMechanismShopaholic-plugin.git --want=dev-master --oc`
   ```

2. **Via OctoberCMS Marketplace**
   - Not yet available

3. **Manual Installation**
   - Download the plugin
   - Extract to `/plugins/logingrupa/extendpromomechanism`
   - Run `php artisan plugin:refresh Logingrupa.ExtendPromoMechanism`

## Usage

1. Go to **Promotions → Promo Mechanisms** in your OctoberCMS backend
2. Click **Create**
3. Select the mechanism type **"Set exact price when total quantity ≥ limit"**
4. Set the **Target price per item** (the exact price you want each qualifying item to cost)
5. Set the **Minimum total quantity** to trigger the discount
6. Configure other standard settings like active dates, product restrictions, etc.
7. Save and test your new promotion
8. Do not frget to **enable Promo Mechanism**  using **Camaigns**  or **Cupon groups** 

## How It Works

When a customer adds items to their cart:

1. The plugin checks if the total quantity of items in the cart exceeds the specified minimum
2. If the threshold is met, it applies the target price to all eligible items
3. The target price is only applied to items whose original price is higher than the target price
4. Items already priced below the target price remain unchanged

## Example

**Scenario**: You want all products in specific category to be priced at exactly $7.20 each when a customer has 20 or more items in their cart.

1. Create a new promo mechanism
2. Select **"Set exact price when total quantity ≥ limit"**
3. Set **Target price per item** to "7.20"
4. Set **Minimum total quantity** to "20"
5. Save

Now, when a customer adds 20 or more eligible products to their cart, all products will be priced at $7.20 each (if their original price was higher than $7.20).

## License

MIT License

## Author

Created by Logingrupa

## Support

If you have any questions or issues, please create an issue on GitHub.