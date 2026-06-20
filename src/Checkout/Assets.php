<?php

declare(strict_types=1);

namespace Robothead\WcConditionalFields\Checkout;

use Robothead\WcConditionalFields\Data\MethodRuleRepository;

final class Assets {

	public function __construct(
		private readonly MethodRuleRepository $repository,
	) {}

	public function register(): void {
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue' ] );
	}

	/**
	 * Builds the rules map for JS, adding a bare method-ID entry alongside each
	 * "method_id:instance_id" key. This covers shipping plugins (e.g. EABI) that
	 * strip the instance suffix from the rate ID they emit, so the radio button
	 * value never contains a colon.
	 *
	 * @return array<string, list<string>>
	 */
	private function buildJsRules(): array {
		$rules = $this->repository->getRules();

		foreach ( $rules as $key => $hidden ) {
			$methodId = strstr( $key, ':', true );

			if ( $methodId !== false && ! isset( $rules[ $methodId ] ) ) {
				$rules[ $methodId ] = $hidden;
			}
		}

		return $rules;
	}

	public function enqueue(): void {
		if ( ! is_checkout() ) {
			return;
		}

		wp_enqueue_script(
			'wcsf-conditional-fields',
			WCSF_URL . 'assets/js/conditional-fields.js',
			[ 'jquery', 'wc-checkout' ],
			WCSF_VERSION,
			true
		);

		wp_localize_script(
			'wcsf-conditional-fields',
			'wcsfData',
			[ 'rules' => $this->buildJsRules() ]
		);
	}
}
