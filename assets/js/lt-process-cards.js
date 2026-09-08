/**
 * LT Process Cards - front-end behaviour.
 *
 * Placeholder only. Interaction logic is added in a later phase. The
 * registration pattern below is already in place so that every future handler
 * is scoped to a single widget instance and re-initialises correctly inside the
 * Elementor editor.
 */
( function ( $ ) {
	'use strict';

	/**
	 * Initialise a single Booking Card instance.
	 *
	 * @param {jQuery} $scope The widget wrapper, supplied by Elementor.
	 */
	function initBookingCard( $scope ) {
		if ( ! $scope || ! $scope.length ) {
			return;
		}

		// Intentionally empty for now.
	}

	$( window ).on( 'elementor/frontend/init', function () {
		if ( 'undefined' === typeof elementorFrontend ) {
			return;
		}

		elementorFrontend.hooks.addAction(
			'frontend/element_ready/lt_booking_card.default',
			initBookingCard
		);
	} );
}( jQuery ) );
