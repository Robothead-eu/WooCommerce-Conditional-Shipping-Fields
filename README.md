# WooCommerce Conditional Shipping Fields

Streamline checkout by hiding irrelevant address fields based on the chosen shipping method. When a customer selects a parcel machine or pickup point, street address and city fields serve no purpose — this plugin removes them automatically, reducing friction and the chance of abandonment.

## How it works

Configuration lives directly on each shipping method inside **WooCommerce → Settings → Shipping → [your zone] → edit a method**. A "Hide checkout fields" multiselect appears at the bottom of the settings form. Whatever you select there gets hidden — and made non-required — when a customer chooses that method at checkout.

Both billing and shipping fields are supported. Email is intentionally excluded from the list since it is always needed for order confirmation.

Field visibility is applied in two places:

- **Client side** — fields are shown or hidden instantly as the customer changes the shipping method, with no page reload.
- **Server side** — the `required` constraint is stripped from hidden fields before WooCommerce validates the submission, so the order can go through without them.

## Requirements

- PHP 8.1+
- WordPress 6.0+
- WooCommerce 7.0+

## Installation

1. Upload the `woocommerce-conditional-shipping-fields` folder to `wp-content/plugins/`.
2. Activate the plugin in **Plugins → Installed Plugins**.
3. Go to **WooCommerce → Settings → Shipping**, open a shipping zone, edit any method and configure the fields to hide.

If you use Composer, run `composer install` inside the plugin folder to generate the optimised autoloader. The plugin includes a fallback PSR-4 autoloader so Composer is not required.

## Extending

You can add or remove fields from the available list using the `wcsf_available_fields` filter:

```php
add_filter( 'wcsf_available_fields', function ( array $fields ): array {
    // Remove a field so it can never be hidden.
    unset( $fields['billing_phone'] );

    // Add a custom field from another plugin.
    $fields['billing_vat_number'] = __( 'Billing: VAT number' );

    return $fields;
} );
```

## License

[GPL-2.0-or-later](https://www.gnu.org/licenses/gpl-2.0.html)
