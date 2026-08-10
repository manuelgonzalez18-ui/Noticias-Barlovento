/*
 * Movimiento continuo del ticker de Noticias Barlovento.
 * Usa scrollLeft para no competir con las animaciones/transform de NewsExo.
 */
( function () {
	'use strict';

	function iniciarTicker() {
		var wrapper = document.querySelector( '.trending-news-area .news-marquee-wrapper' );
		var track = wrapper ? wrapper.querySelector( '.news-highlights' ) : null;

		if ( ! wrapper || ! track || wrapper.dataset.nbTicker === 'activo' ) {
			return;
		}

		var originales = Array.prototype.slice.call( track.children );

		if ( originales.length < 2 ) {
			return;
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
		var velocidad = 42; // Pixeles por segundo: legible y continuo.
		var anchoCiclo = 0;

		function recalcular() {
			var primero = originales[ 0 ];
			var ultimo = originales[ originales.length - 1 ];

			if ( primero && ultimo ) {
				var inicio = primero.offsetLeft;
				var fin = ultimo.offsetLeft + ultimo.offsetWidth;
				anchoCiclo = Math.max( 1, fin - inicio );
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

			if ( ! pausado && anchoCiclo > 0 ) {
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
		window.addEventListener( 'load', recalcular, { once: true } );

		var timeout;
		window.addEventListener( 'resize', function () {
			window.clearTimeout( timeout );
			timeout = window.setTimeout( recalcular, 150 );
		} );

		if ( ! window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) {
			window.requestAnimationFrame( animar );
		}
	}

	function intentarInicio() {
		iniciarTicker();

		/* NewsExo puede terminar de montar el bloque despues de DOMContentLoaded. */
		var intentos = 0;
		var temporizador = window.setInterval( function () {
			intentos += 1;
			iniciarTicker();

			if ( document.querySelector( '.news-marquee-wrapper[data-nb-ticker="activo"]' ) || intentos >= 20 ) {
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
