/*
 * Simplifica la franja superior de NewsExo y conserva solo las redes sociales.
 * Se basa en la presencia real de enlaces sociales para no depender de una
 * clase concreta del tema que pueda cambiar entre versiones.
 */
( function () {
	'use strict';

	var dominiosSociales = [
		'facebook.com',
		'fb.com',
		'twitter.com',
		'x.com',
		'instagram.com',
		'linkedin.com',
		'youtube.com',
		'youtu.be',
		'tiktok.com',
		't.me',
		'telegram.me',
		'threads.net',
		'bsky.app',
		'wa.me',
		'whatsapp.com'
	];

	function esEnlaceSocial( enlace ) {
		var href = ( enlace.getAttribute( 'href' ) || '' ).toLowerCase();
		var firma = ( enlace.className + ' ' + enlace.innerHTML ).toLowerCase();

		if ( dominiosSociales.some( function ( dominio ) { return href.indexOf( dominio ) !== -1; } ) ) {
			return true;
		}

		return /(facebook|twitter|instagram|linkedin|youtube|tiktok|telegram|whatsapp|google-plus|google\+|x-twitter)/.test( firma );
	}

	function enlacesSocialesAntesDelMenu() {
		var menu = document.querySelector( '.navbar, .main-navigation, nav.navigation-primary' );
		var limite = menu ? menu.getBoundingClientRect().top + 2 : window.innerHeight;

		return Array.prototype.slice.call( document.querySelectorAll( 'a' ) ).filter( function ( enlace ) {
			return esEnlaceSocial( enlace ) && enlace.getBoundingClientRect().top < limite;
		} );
	}

	function ancestroComun( elementos ) {
		if ( ! elementos.length ) {
			return null;
		}

		var candidato = elementos[ 0 ].parentElement;

		while ( candidato && candidato !== document.body ) {
			var contieneTodos = elementos.every( function ( elemento ) {
				return candidato.contains( elemento );
			} );

			if ( contieneTodos ) {
				return candidato;
			}

			candidato = candidato.parentElement;
		}

		return null;
	}

	function encontrarFranja( sociales ) {
		var menu = document.querySelector( '.navbar, .main-navigation, nav.navigation-primary' );
		var limiteMenu = menu ? menu.getBoundingClientRect().top : Number.POSITIVE_INFINITY;
		var actual = ancestroComun( sociales );
		var mejor = actual;

		while ( actual && actual.parentElement && actual.parentElement !== document.body ) {
			var padre = actual.parentElement;
			var rect = padre.getBoundingClientRect();

			/*
			 * No subimos hasta el header completo: la franja superior es baja y
			 * termina antes de la zona del logo/menu.
			 */
			if ( rect.height > 220 || rect.bottom > limiteMenu ) {
				break;
			}

			mejor = padre;
			actual = padre;
		}

		return mejor;
	}

	function contieneSocial( elemento, sociales ) {
		return sociales.some( function ( enlace ) {
			return elemento === enlace || elemento.contains( enlace );
		} );
	}

	function simplificarCabecera() {
		var sociales = enlacesSocialesAntesDelMenu();

		if ( sociales.length < 2 ) {
			return false;
		}

		var franja = encontrarFranja( sociales );

		if ( ! franja || franja.classList.contains( 'nb-topbar-social-only' ) ) {
			return !! franja;
		}

		franja.classList.add( 'nb-topbar-social-only' );

		sociales.forEach( function ( enlace ) {
			enlace.classList.add( 'nb-topbar-social-link' );
		} );

		/* Oculta columnas/widgets de reloj, publicidad vacia u otros extras. */
		Array.prototype.forEach.call(
			franja.querySelectorAll( '.widget, [class*="col-"], [class*="header-widget"], [class*="top-widget"]' ),
			function ( elemento ) {
				if ( contieneSocial( elemento, sociales ) ) {
					elemento.classList.add( 'nb-topbar-redes' );
				} else {
					elemento.classList.add( 'nb-topbar-ocultar' );
				}
			}
		);

		/* Como respaldo, elimina de la franja cualquier reloj que quede suelto. */
		Array.prototype.forEach.call(
			franja.querySelectorAll( '[class*="clock"], [class*="time"], [class*="date"]' ),
			function ( elemento ) {
				if ( ! contieneSocial( elemento, sociales ) ) {
					elemento.classList.add( 'nb-topbar-ocultar' );
				}
			}
		);

		return true;
	}

	function iniciar() {
		if ( simplificarCabecera() ) {
			return;
		}

		var intentos = 0;
		var temporizador = window.setInterval( function () {
			intentos += 1;

			if ( simplificarCabecera() || intentos >= 20 ) {
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
