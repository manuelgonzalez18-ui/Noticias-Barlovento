/*
 * Movimiento continuo del ticker de Noticias Barlovento.
 * Duplica visualmente los titulares para obtener un bucle sin saltos.
 */
( function () {
	'use strict';

	function iniciarTicker() {
		var track = document.querySelector( '.trending-news-area .news-marquee-wrapper .news-highlights' );

		if ( ! track || track.dataset.nbTicker === 'activo' ) {
			return;
		}

		var originales = Array.prototype.slice.call( track.children );

		if ( originales.length < 2 ) {
			return;
		}

		track.dataset.nbTicker = 'activo';

		var anchoOriginal = track.scrollWidth;
		var fragmento = document.createDocumentFragment();

		originales.forEach( function ( item ) {
			var copia = item.cloneNode( true );
			copia.setAttribute( 'aria-hidden', 'true' );

			Array.prototype.forEach.call( copia.querySelectorAll( 'a, button, input, select, textarea, [tabindex]' ), function ( elemento ) {
				elemento.setAttribute( 'tabindex', '-1' );
			} );

			fragmento.appendChild( copia );
		} );

		track.appendChild( fragmento );
		track.classList.add( 'nb-ticker-activo' );

		function recalcular() {
			var distancia = anchoOriginal;

			if ( originales.length ) {
				var primero = originales[ 0 ].getBoundingClientRect();
				var ultima = originales[ originales.length - 1 ].getBoundingClientRect();
				distancia = Math.max( 1, ultima.right - primero.left );
			}

			var segundos = Math.max( 18, Math.min( 42, distancia / 70 ) );
			track.style.setProperty( '--nb-ticker-distance', distancia + 'px' );
			track.style.setProperty( '--nb-ticker-duration', segundos.toFixed( 2 ) + 's' );
		}

		recalcular();
		window.addEventListener( 'load', recalcular, { once: true } );

		var timeout;
		window.addEventListener( 'resize', function () {
			window.clearTimeout( timeout );
			timeout = window.setTimeout( recalcular, 150 );
		} );
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', iniciarTicker );
	} else {
		iniciarTicker();
	}
}() );
