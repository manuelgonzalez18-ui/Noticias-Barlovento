/*
 * Ticker continuo de Noticias Barlovento.
 * El contenido viene del plugin y no depende de la categoria de NewsExo.
 */
( function () {
	'use strict';

	function obtenerItems() {
		if ( window.nbTickerData && Array.isArray( window.nbTickerData.items ) ) {
			return window.nbTickerData.items.filter( function ( item ) {
				return item && item.title && item.url;
			} );
		}

		return [];
	}

	function construirTrack( track, items ) {
		if ( items.length < 2 ) {
			return Array.prototype.slice.call( track.children );
		}

		track.textContent = '';

		items.forEach( function ( item ) {
			var articulo = document.createElement( 'article' );
			var contenido = document.createElement( 'div' );
			var enlace = document.createElement( 'a' );
			var titulo = document.createElement( 'h6' );

			articulo.className = 'news-headline-post nb-ticker-item';
			contenido.className = 'news-headline-post-content';
			enlace.href = item.url;
			titulo.className = 'news-headline-post-title';
			titulo.textContent = item.title;

			enlace.appendChild( titulo );
			contenido.appendChild( enlace );
			articulo.appendChild( contenido );
			track.appendChild( articulo );
		} );

		return Array.prototype.slice.call( track.children );
	}

	function iniciarTicker() {
		var wrapper = document.querySelector( '.trending-news-area .news-marquee-wrapper' );
		var track = wrapper ? wrapper.querySelector( '.news-highlights' ) : null;

		if ( ! wrapper || ! track || wrapper.dataset.nbTicker === 'activo' ) {
			return false;
		}

		var originales = construirTrack( track, obtenerItems() );

		if ( originales.length < 2 ) {
			return false;
		}

		wrapper.dataset.nbTicker = 'activo';
		track.classList.add( 'nb-ticker-activo' );

		var fragmento = document.createDocumentFragment();

		originales.forEach( function ( item ) {
			var copia = item.cloneNode( true );
			copia.setAttribute( 'aria-hidden', 'true' );

			Array.prototype.forEach.call(
				copia.querySelectorAll( 'a, button, input, select, textarea, [tabindex]' ),
				function ( elemento ) {
					elemento.setAttribute( 'tabindex', '-1' );
				}
			);

			fragmento.appendChild( copia );
		} );

		track.appendChild( fragmento );

		var pausado = false;
		var ultimoTiempo = null;
		var velocidad = 55;
		var anchoCiclo = 0;

		function recalcular() {
			var primero = originales[ 0 ];
			var ultimo = originales[ originales.length - 1 ];

			if ( primero && ultimo ) {
				anchoCiclo = Math.max(
					1,
					( ultimo.offsetLeft + ultimo.offsetWidth ) - primero.offsetLeft
				);
			} else {
				anchoCiclo = Math.max( 1, track.scrollWidth / 2 );
			}
		}

		function animar( tiempo ) {
			if ( null === ultimoTiempo ) {
				ultimoTiempo = tiempo;
			}

			var delta = Math.min( 50, tiempo - ultimoTiempo );
			ultimoTiempo = tiempo;

			if ( ! pausado && anchoCiclo > 1 ) {
				wrapper.scrollLeft += velocidad * delta / 1000;

				if ( wrapper.scrollLeft >= anchoCiclo ) {
					wrapper.scrollLeft -= anchoCiclo;
				}
			}

			window.requestAnimationFrame( animar );
		}

		wrapper.addEventListener( 'mouseenter', function () { pausado = true; } );
		wrapper.addEventListener( 'mouseleave', function () { pausado = false; } );
		wrapper.addEventListener( 'focusin', function () { pausado = true; } );
		wrapper.addEventListener( 'focusout', function () { pausado = false; } );

		recalcular();
		window.setTimeout( recalcular, 100 );
		window.addEventListener( 'load', recalcular, { once: true } );

		var timeout;
		window.addEventListener( 'resize', function () {
			window.clearTimeout( timeout );
			timeout = window.setTimeout( recalcular, 150 );
		} );

		if ( ! window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) {
			window.requestAnimationFrame( animar );
		}

		return true;
	}

	function intentarInicio() {
		if ( iniciarTicker() ) {
			return;
		}

		var intentos = 0;
		var temporizador = window.setInterval( function () {
			intentos += 1;

			if ( iniciarTicker() || intentos >= 40 ) {
				window.clearInterval( temporizador );
			}
		}, 250 );
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', intentarInicio );
	} else {
		intentarInicio();
	}
}() );
