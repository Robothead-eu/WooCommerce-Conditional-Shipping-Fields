<?php

declare(strict_types=1);

namespace Robothead\WcConditionalFields\Checkout;

use Robothead\WcConditionalFields\Data\MethodRuleRepository;

final class FieldController {

	public function __construct(
		private readonly MethodRuleRepository $repository,
	) {}

	public function register(): void {
		add_filter( 'woocommerce_checkout_fields', [ $this, 'stripRequiredFromHiddenFields' ] );
	}

	/**
	 * Removes the `required` flag only from fields that are configured as hidden
	 * for the currently chosen shipping method. WooCommerce's built-in validation
	 * then handles all remaining required fields normally — no second pass needed.
	 *
	 * @param array<string, array<string, mixed>> $fields
	 * @return array<string, array<string, mixed>>
	 */
	public function stripRequiredFromHiddenFields( array $fields ): array {
		$hidden = $this->resolveHiddenFields();

		if ( $hidden === [] ) {
			return $fields;
		}

		foreach ( [ 'billing', 'shipping' ] as $group ) {
			if ( empty( $fields[ $group ] ) ) {
				continue;
			}

			foreach ( $fields[ $group ] as $key => &$field ) {
				if ( in_array( $key, $hidden, true ) ) {
					$field['required'] = false;
				}
			}
			unset( $field );
		}

		return $fields;
	}

	/**
	 * Returns the hidden field keys for the current shipping method.
	 *
	 * During checkout submission (POST) we read from $_POST so the strip
	 * matches the method the customer actually submitted. During page render
	 * (GET) we fall back to the session.
	 *
	 * @return list<string>
	 */
	private function resolveHiddenFields(): array {
		$rateId = $this->resolveRateId();

		if ( $rateId === '' ) {
			return [];
		}

		return $this->repository->getHiddenFieldsForRate( $rateId );
	}

	private function resolveRateId(): string {
		// POST takes precedence during checkout submission.
		if (
			! empty( $_POST['shipping_method'] )
			&& is_array( $_POST['shipping_method'] )
			&& isset( $_POST['shipping_method'][0] )
		) {
			return sanitize_text_field( wp_unslash( (string) $_POST['shipping_method'][0] ) );
		}

		$session = WC()->session;

		if ( $session === null ) {
			return '';
		}

		$chosen = $session->get( 'chosen_shipping_methods' );

		if ( empty( $chosen ) || ! is_array( $chosen ) ) {
			return '';
		}

		return (string) $chosen[0];
	}
}
