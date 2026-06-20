<?php

declare(strict_types=1);

namespace Robothead\WcConditionalFields\Data;

final class MethodRuleRepository {

	/** @var array<string, list<string>>|null Lazily-built map: "method_id:instance_id" => hidden field keys. */
	private ?array $rules = null;

	/**
	 * Returns all configured rules across every shipping zone.
	 * Only shipping method instances that have at least one hidden field are included.
	 *
	 * Keys use WooCommerce's rate-ID format: "method_id:instance_id"  (e.g. "eabi_omniva_parcelterminal:3").
	 *
	 * @return array<string, list<string>>
	 */
	public function getRules(): array {
		if ( $this->rules !== null ) {
			return $this->rules;
		}

		$this->rules = [];

		$zones = \WC_Shipping_Zones::get_zones();

		// Zone 0 is the "Rest of the world" / no-zone fallback — not included in get_zones().
		$zones[] = [ 'zone_id' => 0 ];

		foreach ( $zones as $zone_data ) {
			$zone = new \WC_Shipping_Zone( (int) $zone_data['zone_id'] );

			foreach ( $zone->get_shipping_methods( true ) as $method ) {
				$hidden = $method->get_instance_option( 'wcsf_hidden_fields' );

				if ( ! is_array( $hidden ) || $hidden === [] ) {
					continue;
				}

				$rate_id               = $method->id . ':' . $method->get_instance_id();
				$this->rules[$rate_id] = array_values( array_map( 'strval', $hidden ) );
			}
		}

		return $this->rules;
	}

	/**
	 * Returns the hidden field keys for a specific rate ID, or an empty array if none are configured.
	 *
	 * @param string $rateId  e.g. "eabi_omniva_parcelterminal:3"
	 * @return list<string>
	 */
	public function getHiddenFieldsForRate( string $rateId ): array {
		return $this->getRules()[ $rateId ] ?? [];
	}
}
