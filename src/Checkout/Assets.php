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
			[ 'rules' => $this->repository->getRules() ]
		);
	}
}
