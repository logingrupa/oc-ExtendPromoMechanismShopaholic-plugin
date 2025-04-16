# Shopaholic PromoMechanism Extension

## Introduction

This plugin extends the Shopaholic PromoMechanism system with a powerful "Target Price by Quantity" discount mechanism. Unlike traditional percentage or fixed amount discounts, this mechanism allows you to set an **exact price** for items when customers reach a specific quantity threshold.

![Target Price Mechanism](https://example.com/target-price-screenshot.png)

Perfect for bulk order incentives, this plugin helps you encourage customers to purchase larger quantities by offering better per-unit pricing.

## Features

- **Target Price Mechanism**: Sets an exact price for items when a quantity threshold is met, rather than applying a percentage or fixed amount discount
- **Quantity-Based Triggering**: Applies the target price only when the total quantity of items in the cart meets or exceeds the specified threshold
- **Seamless Integration**: Works with the existing Shopaholic PromoMechanism system, including Campaigns and Coupons
- **Tax-Aware Pricing**: Handles tax calculations correctly when applying the target price
- **User-Friendly Backend**: Extends the OctoberCMS backend with clear labels, helpful descriptions, and informational panels
- **Flexible Configuration**: Compatible with other Shopaholic promotional features and restrictions
- **Bulk Order Incentives**: Perfect for encouraging customers to purchase larger quantities by offering better per-unit pricing

## Requirements

- OctoberCMS
- Lovata.Shopaholic plugin
- Lovata.OrdersShopaholic plugin

## Installation

### Via Composer

```bash
php artisan plugin:install Logingrupa.ExtendPromoMechanism --from=git@github.com:logingrupa/oc-ExtendPromoMechanismShopaholic-plugin.git --want=dev-master --oc
```

### Via OctoberCMS Marketplace

- Not yet available

### Manual Installation

1. Download the plugin
2. Extract to `/plugins/logingrupa/extendpromomechanism`
3. Run `php artisan plugin:refresh Logingrupa.ExtendPromoMechanism`

## Usage

1. Go to **Promotions → Promo Mechanisms** in your OctoberCMS backend
2. Click **Create**
3. Select the mechanism type **"Set exact price when total quantity ≥ limit"**
4. Set the **Target price per item** (the exact price you want each qualifying item to cost)
5. Set the **Minimum total quantity** to trigger the discount
6. Configure other standard settings like active dates, product restrictions, etc.
7. Save and test your new promotion
8. Do not forget to **enable Promo Mechanism** using **Campaigns** or **Coupon groups**

![Configuration Screenshot](https://example.com/configuration-screenshot.png)

## Use Cases

### Bulk Purchase Incentives

Encourage customers to buy more items by offering a better per-unit price when they reach a certain quantity threshold.

**Example**: Set items to cost $7.20 each when a customer has 20 or more items in their cart.

### Clearance Sales

Move inventory quickly by setting a specific target price that applies only when customers purchase a minimum quantity.

**Example**: Set all clearance t-shirts to $5 each when customers buy 5 or more.

### Wholesale Pricing

Offer wholesale pricing to retail customers who purchase in bulk without needing separate wholesale accounts.

**Example**: Set products to wholesale price ($12.50 each) when customers purchase 50+ units.

## Configuration

### Target Price Settings

| Setting | Description |
|---------|-------------|
| Target price per item | The exact price you want each qualifying item to cost |
| Minimum total quantity | The total quantity of items required to trigger the discount |

### Compatibility with Other Restrictions

This mechanism works with all standard Shopaholic restrictions:

- Product restrictions
- Time period restrictions
- User group restrictions
- Shipping type restrictions

## Troubleshooting

### Common Issues

**Issue**: Discount not applying to products
**Solution**: Ensure the promo mechanism is enabled via a Campaign or Coupon group

**Issue**: Target price higher than original price
**Solution**: The target price will only be applied if it's lower than the original price

### FAQ

**Q: Can I use this with other discount types?**
A: Yes, this mechanism can be combined with other Shopaholic discount mechanisms.

**Q: Does this work with product variants?**
A: Yes, it works with all product types including variants.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This plugin is licensed under the MIT License - see the LICENSE file for details.

## Credits

- Developed by [Logingrupa](https://logingrupa.lv)
- Built for the [Shopaholic](https://octobercms.com/plugin/lovata-shopaholic) e-commerce ecosystem



[![Latest Version](https://img.shields.io/github/v/release/logingrupa/oc-ExtendPromoMechanismShopaholic-plugin)](https://github.com/logingrupa/oc-ExtendPromoMechanismShopaholic-plugin/releases)
[![License](https://img.shields.io/github/license/logingrupa/oc-ExtendPromoMechanismShopaholic-plugin)](https://github.com/logingrupa/oc-ExtendPromoMechanismShopaholic-plugin/blob/master/LICENSE)
[![OctoberCMS Marketplace](https://img.shields.io/badge/OctoberCMS-Marketplace-orange)](https://octobercms.com/plugin/logingrupa-extendpromomechanism)
