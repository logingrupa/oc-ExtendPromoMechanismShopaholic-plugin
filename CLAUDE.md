# Logingrupa.ExtendPromoMechanism

Adds a custom promo mechanism to Lovata.OrdersShopaholic: SpecificPriceByQuantityDiscountPosition
(specific price when a cart position reaches a quantity). Namespace
Logingrupa\ExtendPromoMechanism, composer package logingrupa/oc-extendpromomechanism-plugin.
Requires Lovata.OrdersShopaholic + Lovata.Shopaholic. Readme.md has usage notes.

## Environment

- Parent app: C:\laragon\www\nc.
- This plugin dir is its OWN git repo - commit here, not in the root repo.

## Architecture map

- classes/event/           ExtendPromoMechanismHandler (registers the mechanism via
                           PromoMechanismStore::EVENT_ADD_PROMO_MECHANISM_CLASS),
                           ExtendPromoMechanismFieldsHandler (backend form fields)
- classes/promomechanism/  QuantityChecker (quantity threshold logic),
                           specificpricebyquantity/SpecificPriceByQuantityDiscountPosition
                           (every unit at the target price once the total reaches the limit),
                           bundleprice/BundlePriceDiscountPosition (every full bundle of
                           quantity_limit units costs discount_value in total) +
                           BundleUnitPriceAllocator (pools units by position id, cent split)
- tests/unit/              Pest: `php vendor/bin/pest -c plugins/logingrupa/extendpromomechanism/phpunit.xml`
                           from the nc root
- partials/                backend form partials
- lang/en/                 lang strings
- updates/                 version.yaml only (no migrations)

## Quality gates

Pest unit tests cover the bundle mechanism only; lint does not cover this dir.
composer lint does NOT cover this plugin (phpcs.xml scope excludes plugins/logingrupa) - fix
phpcs.xml scope or lint manually; `vendor/bin/phpcs --standard=phpcs.xml <plugin path>` won't
work either since the ruleset pins files; note as known gap.

## Ship

Ship via /nc-ship (root CLAUDE.md release flow); package logingrupa/oc-extendpromomechanism-plugin.

## Conventions

Root CLAUDE.md governs: Hungarian notation, Store -> Collection -> Item read path, Tiger-Style.

## Gotchas

- Plugin.php uses lovata.extendpromomechanism::lang.* keys - that namespace is never
  registered (October registers logingrupa.extendpromomechanism), so keys render as raw
  strings in backend. Fix = rename keys to logingrupa. prefix.
- Order totals are recalculated live from the order_promo_mechanism snapshot (type + property),
  so changing what an existing class does reprices old orders. New behaviour = new class; never
  rename or remove a class (a missing class stops the order processor for that order).
- Plugin.php subscribes handlers as INSTANCES (`Event::subscribe(new Handler())`), not
  class names - keep that style here or migrate both handlers together.
