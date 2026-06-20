<?php

declare(strict_types=1);

namespace Robothead\WcConditionalFields\Admin;

final class ShippingInstanceFields {

	private bool $filtersRegistered = false;

	public function register(): void {
		// WooCommerce applies the filter per method ID, not generically.
		// We piggyback on woocommerce_shipping_methods to discover every registered
		// method ID and register a filter for each at the right moment.
		add_filter( 'woocommerce_shipping_methods', [ $this, 'registerPerMethodFilters' ], 999 );
	}

	/**
	 * Registers an instance-form-fields filter for every shipping method that WooCommerce knows about.
	 *
	 * @param array<string, class-string> $methods
	 * @return array<string, class-string>
	 */
	public function registerPerMethodFilters( array $methods ): array {
		if ( $this->filtersRegistered ) {
			return $methods;
		}

		$this->filtersRegistered = true;

		foreach ( array_keys( $methods ) as $methodId ) {
			add_filter(
				'woocommerce_shipping_instance_form_fields_' . $methodId,
				[ $this, 'addFields' ]
			);
		}

		return $methods;
	}

	/**
	 * Injects the hidden-fields multiselect into a shipping method's settings form.
	 *
	 * @param array<string, mixed> $fields  Existing instance form fields.
	 * @return array<string, mixed>
	 */
	public function addFields( array $fields ): array {
		$fields['wcsf_hidden_fields'] = [
			'title'       => __( 'Hide checkout fields', 'woocommerce-conditional-shipping-fields' ),
			'type'        => 'multiselect',
			'class'       => 'wc-enhanced-select',
			'description' => __( 'Fields selected here will be hidden and not required when this shipping method is chosen at checkout.', 'woocommerce-conditional-shipping-fields' ),
			'options'     => $this->getAvailableFields(),
			'default'     => [],
		];

		return $fields;
	}

	/**
	 * Returns the list of checkout field keys that can be hidden.
	 * Extend via the wcsf_available_fields filter.
	 *
	 * @return array<string, string>
	 */
	private function getAvailableFields(): array {
		$fields = [
			// Billing address fields. Email is intentionally excluded — always required for order records.
			'billing_first_name' => __( 'Billing: First name', 'woocommerce-conditional-shipping-fields' ),
			'billing_last_name'  => __( 'Billing: Last name', 'woocommerce-conditional-shipping-fields' ),
			'billing_company'    => __( 'Billing: Company', 'woocommerce-conditional-shipping-fields' ),
			'billing_address_1'  => __( 'Billing: Address line 1', 'woocommerce-conditional-shipping-fields' ),
			'billing_address_2'  => __( 'Billing: Address line 2', 'woocommerce-conditional-shipping-fields' ),
			'billing_city'       => __( 'Billing: City', 'woocommerce-conditional-shipping-fields' ),
			'billing_postcode'   => __( 'Billing: Postcode', 'woocommerce-conditional-shipping-fields' ),
			'billing_state'      => __( 'Billing: State / County', 'woocommerce-conditional-shipping-fields' ),
			'billing_phone'      => __( 'Billing: Phone', 'woocommerce-conditional-shipping-fields' ),
			// Shipping address fields
			'shipping_first_name' => __( 'Shipping: First name', 'woocommerce-conditional-shipping-fields' ),
			'shipping_last_name'  => __( 'Shipping: Last name', 'woocommerce-conditional-shipping-fields' ),
			'shipping_company'    => __( 'Shipping: Company', 'woocommerce-conditional-shipping-fields' ),
			'shipping_address_1'  => __( 'Shipping: Address line 1', 'woocommerce-conditional-shipping-fields' ),
			'shipping_address_2'  => __( 'Shipping: Address line 2', 'woocommerce-conditional-shipping-fields' ),
			'shipping_city'       => __( 'Shipping: City', 'woocommerce-conditional-shipping-fields' ),
			'shipping_postcode'   => __( 'Shipping: Postcode', 'woocommerce-conditional-shipping-fields' ),
			'shipping_state'      => __( 'Shipping: State / County', 'woocommerce-conditional-shipping-fields' ),
			'shipping_phone'      => __( 'Shipping: Phone', 'woocommerce-conditional-shipping-fields' ),
		];

		/**
		 * Filter the checkout fields available for hiding per shipping method.
		 *
		 * @param array<string, string> $fields Field key => human-readable label.
		 */
		return (array) apply_filters( 'wcsf_available_fields', $fields );
	}
}
