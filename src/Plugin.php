<?php

declare(strict_types=1);

namespace Robothead\WcConditionalFields;

use Robothead\WcConditionalFields\Admin\ShippingInstanceFields;
use Robothead\WcConditionalFields\Checkout\Assets;
use Robothead\WcConditionalFields\Checkout\FieldController;
use Robothead\WcConditionalFields\Data\MethodRuleRepository;

final class Plugin {

	private static ?self $instance = null;

	private function __construct(
		private readonly ShippingInstanceFields $adminFields,
		private readonly FieldController $fieldController,
		private readonly Assets $assets,
	) {}

	public static function instance(): self {
		if ( self::$instance === null ) {
			$repository      = new MethodRuleRepository();
			self::$instance  = new self(
				new ShippingInstanceFields(),
				new FieldController( $repository ),
				new Assets( $repository ),
			);
		}

		return self::$instance;
	}

	public function init(): void {
		$this->adminFields->register();
		$this->fieldController->register();
		$this->assets->register();
	}
}
