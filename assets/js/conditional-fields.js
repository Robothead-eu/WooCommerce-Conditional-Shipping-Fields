/* global jQuery, wcsfData */
( function ( $ ) {
	'use strict';

	/** @type {Object.<string, string[]>} */
	var rules = ( wcsfData && wcsfData.rules ) ? wcsfData.rules : {};

	/**
	 * All field keys that appear in at least one rule.
	 * These are the only fields this script ever touches.
	 *
	 * @type {string[]}
	 */
	var managedFields = Object.values( rules ).reduce( function ( acc, fields ) {
		fields.forEach( function ( key ) {
			if ( acc.indexOf( key ) === -1 ) {
				acc.push( key );
			}
		} );
		return acc;
	}, [] );

	/**
	 * Returns the WooCommerce rate ID of the currently selected shipping method.
	 * Returns null when nothing is selected yet.
	 *
	 * WooCommerce renders a radio when multiple methods exist, but a hidden input
	 * when only one method is available — we need to handle both.
	 *
	 * @returns {string|null}
	 */
	function getChosenRateId() {
		var $input = $( 'input[name^="shipping_method"]:checked, input[type="hidden"][name^="shipping_method"]' ).first();
		return $input.length ? $input.val() : null;
	}

	/**
	 * Shows or hides managed fields based on the chosen shipping method.
	 * Also toggles the HTML `required` attribute so the browser's native
	 * validation matches the visibility state.
	 */
	function applyVisibility() {
		if ( managedFields.length === 0 ) {
			return;
		}

		var rateId      = getChosenRateId();
		var hiddenFields = ( rateId && rules[ rateId ] ) ? rules[ rateId ] : [];

		managedFields.forEach( function ( fieldKey ) {
			var $wrapper = $( '#' + fieldKey + '_field' );

			if ( ! $wrapper.length ) {
				return;
			}

			if ( hiddenFields.indexOf( fieldKey ) !== -1 ) {
				$wrapper.hide();
				$wrapper.find( 'input, select, textarea' ).prop( 'required', false );
			} else {
				$wrapper.show();
				// Restore `required` — WooCommerce marks the wrapper <p> with .validate-required, not the input itself.
				if ( $wrapper.hasClass( 'validate-required' ) ) {
					$wrapper.find( 'input, select, textarea' ).prop( 'required', true );
				}
			}
		} );
	}

	$( function () {
		// Re-apply after every WooCommerce checkout AJAX refresh.
		$( document.body ).on( 'updated_checkout', applyVisibility );

		// Re-apply immediately when the customer picks a different method.
		$( document.body ).on( 'change', 'input[name^="shipping_method"]', applyVisibility );

		// Initial run for the pre-selected method.
		applyVisibility();
	} );

}( jQuery ) );
