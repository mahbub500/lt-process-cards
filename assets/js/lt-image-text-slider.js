/**
 * LT Image Text Slider - front-end behaviour.
 *
 * Vanilla-JS slider engine (no external library). One instance per
 * `.lt-image-text-slider` wrapper, configured entirely from the data-*
 * attributes the widget's render() prints - see
 * includes/Widgets/Image_Text_Slider.php. Registered the same way as
 * lt-process-cards.js: through elementorFrontend.hooks on
 * `frontend/element_ready/lt_image_text_slider.default`, so it (re)runs
 * correctly both on the live site and inside the Elementor editor canvas.
 */
( function ( $ ) {
	'use strict';

	/**
	 * Elementor's own default responsive breakpoints (max-width, px). The
	 * widget's "Slides to Show" control is itself responsive at these same
	 * breakpoints, so slide-count switching has to match them exactly or the
	 * two would disagree right at the edges.
	 */
	var BREAKPOINTS = {
		mobile: 767,
		tablet: 1024,
	};

	/**
	 * @param {HTMLElement} root The `.lt-image-text-slider` wrapper.
	 */
	function LTImageTextSlider( root ) {
		this.root = root;
		this.viewport = root.querySelector( '.lt-image-text-slider__viewport' );
		this.track = root.querySelector( '.lt-image-text-slider__track' );
		this.slides = Array.prototype.slice.call( root.querySelectorAll( '.lt-image-text-slider__slide' ) );
		this.prevBtn = root.querySelector( '.lt-image-text-slider__arrow--prev' );
		this.nextBtn = root.querySelector( '.lt-image-text-slider__arrow--next' );
		this.dots = Array.prototype.slice.call( root.querySelectorAll( '.lt-image-text-slider__dot' ) );

		this.detailRoot = root.querySelector( '.lt-image-text-slider__detail' );
		this.detailMedia = root.querySelector( '.lt-image-text-slider__detail-media' );
		this.detailContent = root.querySelector( '.lt-image-text-slider__detail-content' );
		this.detailTemplates = { media: {}, content: {} };

		Array.prototype.forEach.call( root.querySelectorAll( '.lt-image-text-slider__detail-media-template' ), function ( tpl ) {
			this.detailTemplates.media[ tpl.getAttribute( 'data-index' ) ] = tpl;
		}, this );

		Array.prototype.forEach.call( root.querySelectorAll( '.lt-image-text-slider__detail-content-template' ), function ( tpl ) {
			this.detailTemplates.content[ tpl.getAttribute( 'data-index' ) ] = tpl;
		}, this );

		this.activeDetailIndex = this.detailRoot ? ( parseInt( this.detailRoot.getAttribute( 'data-active-index' ), 10 ) || 0 ) : -1;
		this.detailSwapTimer = null;

		this.total = this.slides.length;

		if ( ! this.total || ! this.viewport || ! this.track ) {
			return;
		}

		this.effect = root.getAttribute( 'data-effect' ) || 'slide';
		this.speed = parseInt( root.getAttribute( 'data-speed' ), 10 ) || 400;
		this.loop = 'true' === root.getAttribute( 'data-loop' );
		this.drag = 'true' === root.getAttribute( 'data-drag' );
		this.autoplay = 'true' === root.getAttribute( 'data-autoplay' );
		this.autoplaySpeed = parseInt( root.getAttribute( 'data-autoplay-speed' ), 10 ) || 4000;
		this.pauseOnHover = 'true' === root.getAttribute( 'data-pause-on-hover' );
		this.toScroll = parseInt( root.getAttribute( 'data-slides-to-scroll' ), 10 ) || 1;
		this.slidesDesktop = parseInt( root.getAttribute( 'data-slides-desktop' ), 10 ) || 1;
		this.slidesTablet = parseInt( root.getAttribute( 'data-slides-tablet' ), 10 ) || this.slidesDesktop;
		this.slidesMobile = parseInt( root.getAttribute( 'data-slides-mobile' ), 10 ) || 1;

		this.index = Math.min( parseInt( root.getAttribute( 'data-initial-slide' ), 10 ) || 0, this.total - 1 );

		this.isDragging = false;
		this.dragMoved = false;
		this.autoplayTimer = null;

		this.onResize = this.debounce( this.layout.bind( this ), 150 );

		this.init();
	}

	LTImageTextSlider.prototype.init = function () {
		if ( 'fade' === this.effect ) {
			this.root.classList.add( 'lt-image-text-slider--fade' );
		}

		this.bindEvents();
		this.layout();

		if ( this.autoplay && this.maxIndex > 0 ) {
			this.startAutoplay();
		}
	};

	LTImageTextSlider.prototype.getSlidesToShow = function () {
		var width = window.innerWidth;

		if ( width <= BREAKPOINTS.mobile ) {
			return this.slidesMobile;
		}

		if ( width <= BREAKPOINTS.tablet ) {
			return this.slidesTablet;
		}

		return this.slidesDesktop;
	};

	LTImageTextSlider.prototype.getGap = function () {
		var value = window.getComputedStyle( this.root ).getPropertyValue( '--lt-its-gap' );
		var parsed = parseFloat( value );

		return isNaN( parsed ) ? 0 : parsed;
	};

	/**
	 * Recompute slide count and, for the 'slide' effect, every slide's
	 * actual rendered width/offset - without touching the track's position.
	 * Split out from layout() so selectDetail() can re-measure (the active
	 * slide's box just changed size) and then animate into the new
	 * position, instead of layout()'s always-immediate resize behaviour.
	 *
	 * Each slide's width comes from CSS now (the Layout/Active State
	 * sections' Width/Height controls - `.lt-image-text-slider__slide` is
	 * `flex: 0 0 auto`, so it already matches its card), not a uniform
	 * viewport-width division, so slides can have different widths (the
	 * active one) without the offset math falling out of sync.
	 */
	LTImageTextSlider.prototype.measure = function () {
		this.slidesToShow = Math.max( 1, Math.min( this.getSlidesToShow(), this.total ) );
		this.gap = this.getGap();
		this.track.style.gap = this.gap + 'px';

		if ( 'fade' === this.effect ) {
			this.maxIndex = this.total - 1;

			return;
		}

		var offset = 0;

		this.slideWidths = [];
		this.slideOffsets = [];

		this.slides.forEach( function ( slide ) {
			var width = slide.getBoundingClientRect().width;

			this.slideWidths.push( width );
			this.slideOffsets.push( offset );
			offset += width + this.gap;
		}, this );

		this.trackContentWidth = Math.max( 0, offset - this.gap );
		this.maxOffset = Math.max( 0, this.trackContentWidth - this.viewport.clientWidth );

		this.maxIndex = 0;

		for ( var i = this.total - 1; i >= 0; i-- ) {
			if ( this.slideOffsets[ i ] <= this.maxOffset ) {
				this.maxIndex = i;

				break;
			}
		}

		if ( ! this.loop && this.index > this.maxIndex ) {
			this.index = this.maxIndex;
		}
	};

	/**
	 * Recompute slide sizing for the current viewport and re-apply the
	 * current position without animating (a resize is not a navigation).
	 */
	LTImageTextSlider.prototype.layout = function () {
		if ( 'fade' === this.effect ) {
			this.slides.forEach( function ( slide ) {
				slide.style.flex = '0 0 100%';
			} );
		}

		this.measure();
		this.renderFrame( true );
		this.updateArrowState();
		this.updateDots();
	};

	/**
	 * Apply `this.index` to the DOM.
	 *
	 * @param {boolean} immediate Skip the CSS transition (used for layout/resize, never for navigation).
	 */
	LTImageTextSlider.prototype.renderFrame = function ( immediate ) {
		if ( 'fade' === this.effect ) {
			this.slides.forEach( function ( slide, i ) {
				slide.style.transitionDuration = this.speed + 'ms';
				slide.classList.toggle( 'lt-image-text-slider__slide--current', i === this.index );
			}, this );

			return;
		}

		var offset = Math.min( this.slideOffsets[ this.index ] || 0, this.maxOffset );

		this.track.style.transitionDuration = immediate ? '0ms' : this.speed + 'ms';
		this.track.style.transform = 'translateX(' + -offset + 'px)';

		if ( immediate ) {
			// Force a reflow so a later, animated transform change still transitions.
			void this.track.offsetHeight;
			this.track.style.transitionDuration = this.speed + 'ms';
		}
	};

	LTImageTextSlider.prototype.goTo = function ( index, immediate ) {
		if ( this.loop ) {
			index = ( ( index % this.total ) + this.total ) % this.total;
		} else {
			index = Math.max( 0, Math.min( index, this.maxIndex ) );
		}

		this.index = index;
		this.renderFrame( immediate );
		this.updateArrowState();
		this.updateDots();
	};

	LTImageTextSlider.prototype.next = function () {
		var nextIndex = this.index + this.toScroll;

		if ( ! this.loop && nextIndex > this.maxIndex ) {
			nextIndex = this.maxIndex;
		} else if ( this.loop && this.index >= this.maxIndex ) {
			nextIndex = 0;
		}

		this.goTo( nextIndex );
		this.restartAutoplay();
	};

	LTImageTextSlider.prototype.prev = function () {
		var prevIndex = this.index - this.toScroll;

		if ( this.loop && this.index <= 0 ) {
			prevIndex = this.maxIndex;
		}

		this.goTo( prevIndex );
		this.restartAutoplay();
	};

	LTImageTextSlider.prototype.updateArrowState = function () {
		if ( ! this.prevBtn || ! this.nextBtn ) {
			return;
		}

		var noScrollRoom = this.maxIndex <= 0;

		if ( this.loop ) {
			this.prevBtn.disabled = noScrollRoom;
			this.nextBtn.disabled = noScrollRoom;

			return;
		}

		this.prevBtn.disabled = this.index <= 0;
		this.nextBtn.disabled = this.index >= this.maxIndex;
	};

	LTImageTextSlider.prototype.updateDots = function () {
		this.dots.forEach( function ( dot, i ) {
			dot.classList.toggle( 'lt-image-text-slider__dot--active', i === this.index );
		}, this );
	};

	/**
	 * Select a slide as the source for the Detail Panel: highlights it (and
	 * only it) as `--active` (border/shadow only - see the Active State
	 * style section) - then crossfades the Detail Panel over to that
	 * slide's content (swapDetailContent()).
	 *
	 * @param {number} index Zero-based slide index.
	 */
	LTImageTextSlider.prototype.selectDetail = function ( index ) {
		if ( index === this.activeDetailIndex ) {
			return;
		}

		this.activeDetailIndex = index;

		// Every slide keeps the exact same box size (see the CSS file
		// header comment), so toggling --active here only changes its
		// border/shadow - no re-measure/re-render of the track is needed.
		this.slides.forEach( function ( slide, i ) {
			slide.classList.toggle( 'lt-image-text-slider__slide--active', i === index );
		} );

		if ( this.detailRoot ) {
			this.swapDetailContent( index );
		}
	};

	/**
	 * Crossfade the Detail Panel over to a different slide: fade the
	 * current image/text out, swap in that slide's already-escaped,
	 * server-rendered `<template>` markup (see render_detail_panel() in
	 * includes/Widgets/Image_Text_Slider.php) while it's invisible, then
	 * fade back in - instead of an abrupt content swap.
	 * DETAIL_FADE_MS below must stay >= the CSS fade-out transition
	 * duration (`.lt-image-text-slider__detail--switching`, in the
	 * stylesheet) or the swap will happen mid-fade and flash the new
	 * content in early.
	 *
	 * @param {number} index Zero-based slide index.
	 */
	LTImageTextSlider.prototype.swapDetailContent = function ( index ) {
		var self = this;
		var mediaTpl = this.detailTemplates.media[ index ];
		var contentTpl = this.detailTemplates.content[ index ];
		var DETAIL_FADE_MS = 260;

		window.clearTimeout( this.detailSwapTimer );

		this.detailRoot.classList.add( 'lt-image-text-slider__detail--switching' );

		this.detailSwapTimer = window.setTimeout( function () {
			if ( mediaTpl && self.detailMedia ) {
				var existingImg = self.detailMedia.querySelector( 'img' );

				if ( existingImg ) {
					existingImg.remove();
				}

				self.detailMedia.insertBefore( mediaTpl.content.cloneNode( true ), self.detailMedia.firstChild );
			}

			if ( contentTpl && self.detailContent ) {
				self.detailContent.innerHTML = '';
				self.detailContent.appendChild( contentTpl.content.cloneNode( true ) );
			}

			// Force a reflow so removing --switching right after actually
			// transitions the fade-in instead of jumping straight to it.
			void self.detailRoot.offsetHeight;
			self.detailRoot.classList.remove( 'lt-image-text-slider__detail--switching' );
		}, DETAIL_FADE_MS );
	};

	LTImageTextSlider.prototype.startAutoplay = function () {
		this.stopAutoplay();

		var self = this;

		this.autoplayTimer = window.setInterval( function () {
			self.next();
		}, this.autoplaySpeed );
	};

	LTImageTextSlider.prototype.stopAutoplay = function () {
		if ( this.autoplayTimer ) {
			window.clearInterval( this.autoplayTimer );
			this.autoplayTimer = null;
		}
	};

	LTImageTextSlider.prototype.restartAutoplay = function () {
		if ( this.autoplay ) {
			this.startAutoplay();
		}
	};

	LTImageTextSlider.prototype.bindEvents = function () {
		var self = this;

		if ( this.prevBtn ) {
			this.prevBtn.addEventListener( 'click', function () {
				self.prev();
			} );
		}

		if ( this.nextBtn ) {
			this.nextBtn.addEventListener( 'click', function () {
				self.next();
			} );
		}

		this.dots.forEach( function ( dot, i ) {
			dot.addEventListener( 'click', function () {
				self.goTo( i );
				self.restartAutoplay();
			} );
		} );

		if ( this.autoplay && this.pauseOnHover ) {
			this.root.addEventListener( 'mouseenter', function () {
				self.stopAutoplay();
			} );
			this.root.addEventListener( 'mouseleave', function () {
				self.startAutoplay();
			} );
		}

		this.root.setAttribute( 'tabindex', '0' );
		this.root.addEventListener( 'keydown', function ( event ) {
			if ( 'ArrowLeft' === event.key ) {
				self.prev();
			} else if ( 'ArrowRight' === event.key ) {
				self.next();
			}
		} );

		window.addEventListener( 'resize', this.onResize );

		this.bindSelection();

		if ( this.drag ) {
			this.bindDrag();
		}
	};

	/**
	 * Click/keyboard delegation on the track: selects a slide for the
	 * Detail Panel when its card has no Link (a real `<a>` card is left to
	 * navigate normally), and swallows the click a drag ends on so dragging
	 * across a card never also fires a selection or a link.
	 */
	LTImageTextSlider.prototype.bindSelection = function () {
		var self = this;

		function resolveSlide( target ) {
			var cardEl = target.closest( '.lt-image-text-slider__card' );

			if ( ! cardEl || 'a' === cardEl.tagName.toLowerCase() ) {
				return null;
			}

			var slideEl = cardEl.closest( '.lt-image-text-slider__slide' );

			if ( ! slideEl ) {
				return null;
			}

			var index = parseInt( slideEl.getAttribute( 'data-index' ), 10 );

			return isNaN( index ) ? null : index;
		}

		this.track.addEventListener( 'click', function ( event ) {
			if ( self.dragMoved ) {
				// A real drag just ended on this card - suppress both the
				// click-to-select behaviour below and, for a linked slide,
				// the browser's default navigation.
				event.preventDefault();
				self.dragMoved = false;

				return;
			}

			var index = resolveSlide( event.target );

			if ( null !== index ) {
				self.selectDetail( index );
			}
		} );

		this.track.addEventListener( 'keydown', function ( event ) {
			if ( 'Enter' !== event.key && ' ' !== event.key && 'Spacebar' !== event.key ) {
				return;
			}

			var index = resolveSlide( event.target );

			if ( null !== index ) {
				event.preventDefault();
				self.selectDetail( index );
			}
		} );
	};

	/**
	 * Mouse/touch drag-to-navigate. Move/up listeners are attached to
	 * `document` only for the duration of an active drag (added on
	 * pointerdown, removed on pointerup) so nothing accumulates on the
	 * window across repeated Elementor-editor re-renders.
	 */
	LTImageTextSlider.prototype.bindDrag = function () {
		var self = this;
		var startX = 0;
		var startOffset = 0;

		function getPointerX( event ) {
			if ( event.touches && event.touches.length ) {
				return event.touches[ 0 ].clientX;
			}

			if ( event.changedTouches && event.changedTouches.length ) {
				return event.changedTouches[ 0 ].clientX;
			}

			return event.clientX;
		}

		function onMove( event ) {
			if ( ! self.isDragging ) {
				return;
			}

			var delta = getPointerX( event ) - startX;

			if ( Math.abs( delta ) > 5 ) {
				self.dragMoved = true;
			}

			if ( 'slide' === self.effect ) {
				self.track.style.transform = 'translateX(' + ( -startOffset + delta ) + 'px)';
			}
		}

		function onUp( event ) {
			if ( ! self.isDragging ) {
				return;
			}

			self.isDragging = false;
			self.viewport.classList.remove( 'lt-image-text-slider__viewport--dragging' );

			document.removeEventListener( 'mousemove', onMove );
			document.removeEventListener( 'mouseup', onUp );
			document.removeEventListener( 'touchmove', onMove );
			document.removeEventListener( 'touchend', onUp );

			if ( 'slide' !== self.effect ) {
				return;
			}

			var delta = getPointerX( event ) - startX;
			var threshold = ( self.slideWidths[ self.index ] || 0 ) / 4;

			if ( delta < -threshold ) {
				self.next();
			} else if ( delta > threshold ) {
				self.prev();
			} else {
				self.renderFrame();
			}
		}

		function onDown( event ) {
			if ( 'slide' !== self.effect || self.maxIndex <= 0 ) {
				return;
			}

			self.isDragging = true;
			self.dragMoved = false;
			startX = getPointerX( event );
			startOffset = self.slideOffsets[ self.index ] || 0;
			self.track.style.transitionDuration = '0ms';
			self.viewport.classList.add( 'lt-image-text-slider__viewport--dragging' );

			document.addEventListener( 'mousemove', onMove );
			document.addEventListener( 'mouseup', onUp );
			document.addEventListener( 'touchmove', onMove, { passive: true } );
			document.addEventListener( 'touchend', onUp );
		}

		this.viewport.addEventListener( 'mousedown', onDown );
		this.viewport.addEventListener( 'touchstart', onDown, { passive: true } );
	};

	LTImageTextSlider.prototype.debounce = function ( fn, wait ) {
		var timeout;

		return function () {
			var args = arguments;

			window.clearTimeout( timeout );
			timeout = window.setTimeout( function () {
				fn.apply( null, args );
			}, wait );
		};
	};

	/**
	 * Initialise a single Image Text Slider instance.
	 *
	 * @param {jQuery} $scope The widget wrapper, supplied by Elementor.
	 */
	function initImageTextSlider( $scope ) {
		if ( ! $scope || ! $scope.length ) {
			return;
		}

		var root = $scope[ 0 ].querySelector( '.lt-image-text-slider' );

		if ( ! root ) {
			return;
		}

		new LTImageTextSlider( root );
	}

	$( window ).on( 'elementor/frontend/init', function () {
		if ( 'undefined' === typeof elementorFrontend ) {
			return;
		}

		elementorFrontend.hooks.addAction(
			'frontend/element_ready/lt_image_text_slider.default',
			initImageTextSlider
		);
	} );
}( jQuery ) );
