/**
 * LT Process Cards - front-end behaviour.
 *
 * Click/keyboard selection for the booking card's date and time chips.
 * Each chip group is a `role="radiogroup"` of `role="radio"` divs (see
 * render_booking_card() in includes/Widgets/Process_Cards.php) rather than
 * real form controls, so single-choice selection has to be reproduced here:
 * clicking (or pressing Enter/Space on) one chip selects it and clears the
 * `--selected` state from every other chip in its own group. This is purely
 * a visual/aria selection - nothing is submitted or persisted.
 *
 * Registered the same way as the other widgets in this plugin: through
 * elementorFrontend.hooks, scoped to a single widget instance, so it
 * (re)runs correctly both on the live site and inside the Elementor editor
 * canvas, and never affects a second instance of this widget on the same
 * page.
 */
( function ( $ ) {
	'use strict';

	/**
	 * Toggle "selected" on one chip within its group, clearing it from
	 * every sibling - the single-choice behaviour a native radio input
	 * gets for free.
	 *
	 * @param {NodeList}    items         Every chip in the group.
	 * @param {HTMLElement} selected      The chip that was just activated.
	 * @param {string}      selectedClass The chip type's "--selected" BEM modifier class.
	 */
	function selectChip( items, selected, selectedClass ) {
		items.forEach( function ( item ) {
			var isSelected = item === selected;

			item.classList.toggle( selectedClass, isSelected );
			item.setAttribute( 'aria-checked', isSelected ? 'true' : 'false' );
		} );
	}

	/**
	 * A chip only responds to activation if it isn't a disabled time chip
	 * (`aria-disabled="true"`) or a hidden "peek" date chip
	 * (`aria-hidden="true"`) - both already excluded from the tab order in
	 * render_booking_card(); this mirrors that at the click level too.
	 *
	 * @param {HTMLElement} item
	 */
	function isSelectable( item ) {
		return 'true' !== item.getAttribute( 'aria-hidden' ) && 'true' !== item.getAttribute( 'aria-disabled' );
	}

	/**
	 * Wire click + Enter/Space selection for one chip group (dates or times).
	 *
	 * @param {HTMLElement} group         The `role="radiogroup"` wrapper.
	 * @param {string}      itemSelector  CSS selector for the chips inside it.
	 * @param {string}      selectedClass The chip type's "--selected" BEM modifier class.
	 */
	function bindChipGroup( group, itemSelector, selectedClass ) {
		if ( ! group ) {
			return;
		}

		function activate( target ) {
			var item = target.closest( itemSelector );

			if ( ! item || ! group.contains( item ) || ! isSelectable( item ) ) {
				return false;
			}

			selectChip( group.querySelectorAll( itemSelector ), item, selectedClass );

			return true;
		}

		group.addEventListener( 'click', function ( event ) {
			activate( event.target );
		} );

		group.addEventListener( 'keydown', function ( event ) {
			if ( 'Enter' !== event.key && ' ' !== event.key && 'Spacebar' !== event.key ) {
				return;
			}

			if ( activate( event.target ) ) {
				event.preventDefault();
			}
		} );
	}

	/**
	 * Initialise a single Process Cards instance.
	 *
	 * @param {jQuery} $scope The widget wrapper, supplied by Elementor.
	 */
	function initProcessCards( $scope ) {
		if ( ! $scope || ! $scope.length ) {
			return;
		}

		var root = $scope[ 0 ];

		bindChipGroup( root.querySelector( '.lt-process-cards__dates' ), '.lt-process-cards__date', 'lt-process-cards__date--selected' );
		bindChipGroup( root.querySelector( '.lt-process-cards__times' ), '.lt-process-cards__time', 'lt-process-cards__time--selected' );
	}

	$( window ).on( 'elementor/frontend/init', function () {
		if ( 'undefined' === typeof elementorFrontend ) {
			return;
		}

		elementorFrontend.hooks.addAction(
			'frontend/element_ready/lt_process_cards.default',
			initProcessCards
		);
	} );
}( jQuery ) );
