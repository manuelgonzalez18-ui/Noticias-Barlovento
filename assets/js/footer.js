/*
 * Personaliza el crédito del pie de página sin modificar News Gallery.
 */
( function () {
	'use strict';

	var textoPropio = 'COPYRIGHT 2026 Noticias Barlovento';

	function esCreditoTema( elemento ) {
		var texto = ( elemento.textContent || '' ).replace( /\s+/g, ' ' ).trim().toLowerCase();

		if ( ! texto ) {
			return false;
		}

		return texto.indexOf( 'wordpress' ) !== -1 &&
			texto.indexOf( 'themearile' ) !== -1 &&
			( texto.indexOf( 'news gallery' ) !== -1 || texto.indexOf( 'newsexo' ) !== -1 );
	}

	function reemplazarCredito() {
		var zonas = document.querySelectorAll( 'footer, .site-footer, #colophon, .footer-copyright, .site-info' );
		var candidatos = [];

		Array.prototype.forEach.call( zonas, function ( zona ) {
			if ( esCreditoTema( zona ) ) {
				candidatos.push( zona );
			}

			Array.prototype.forEach.call( zona.querySelectorAll( '*' ), function ( elemento ) {
				if ( esCreditoTema( elemento ) ) {
					candidatos.push( elemento );
				}
			} );
		} );

		if ( ! candidatos.length ) {
			return false;
		}

		/*
		 * El nodo más pequeño evita borrar otras columnas del footer si el tema
		 * envuelve el crédito dentro de un contenedor grande.
		 */
		candidatos.sort( function ( a, b ) {
			return ( a.textContent || '' ).length - ( b.textContent || '' ).length;
		} );

		var credito = candidatos[ 0 ];
		credito.textContent = textoPropio;
		credito.classList.add( 'nb-footer-copyright-propio' );
		return true;
	}

	function iniciar() {
		if ( reemplazarCredito() ) {
			return;
		}

		var intentos = 0;
		var temporizador = window.setInterval( function () {
			intentos += 1;

			if ( reemplazarCredito() || intentos >= 20 ) {
				window.clearInterval( temporizador );
			}
		}, 250 );
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', iniciar );
	} else {
		iniciar();
	}
}() );
