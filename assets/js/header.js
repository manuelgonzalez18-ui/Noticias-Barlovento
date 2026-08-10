/*
 * Simplifica la franja superior de NewsExo y muestra solo las redes oficiales.
 */
( function () {
	'use strict';

	var dominiosSociales = [
		'facebook.com', 'fb.com', 'twitter.com', 'x.com', 'instagram.com',
		'linkedin.com', 'youtube.com', 'youtu.be', 'tiktok.com', 't.me',
		'telegram.me', 'threads.net', 'bsky.app', 'wa.me', 'whatsapp.com'
	];

	function redesOficiales() {
		if ( window.nbHeaderData && Array.isArray( window.nbHeaderData.socials ) ) {
			return window.nbHeaderData.socials.filter( function ( red ) {
				return red && red.name && red.url;
			} );
		}

		return [];
	}

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
			if ( elementos.every( function ( elemento ) { return candidato.contains( elemento ); } ) ) {
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

	function encontrarBloqueRedes( franja, sociales ) {
		var candidatos = Array.prototype.slice.call(
			franja.querySelectorAll( '.widget, [class*="col-"], [class*="header-widget"], [class*="top-widget"]' )
		).filter( function ( elemento ) {
			return contieneSocial( elemento, sociales );
		} );

		if ( candidatos.length ) {
			return candidatos.reduce( function ( mejor, actual ) {
				return actual.getBoundingClientRect().width < mejor.getBoundingClientRect().width ? actual : mejor;
			} );
		}

		return ancestroComun( sociales );
	}

	function crearRedesOficiales() {
		var contenedor = document.createElement( 'div' );
		var etiqueta = document.createElement( 'span' );
		var lista = document.createElement( 'div' );

		contenedor.className = 'nb-redes-oficiales';
		etiqueta.className = 'nb-redes-oficiales__etiqueta';
		etiqueta.textContent = 'Síguenos';
		lista.className = 'nb-redes-oficiales__lista';

		redesOficiales().forEach( function ( red ) {
			var enlace = document.createElement( 'a' );
			var icono = document.createElement( 'span' );

			enlace.href = red.url;
			enlace.target = '_blank';
			enlace.rel = 'noopener noreferrer';
			enlace.className = 'nb-topbar-social-link';
			enlace.setAttribute( 'aria-label', red.name );
			enlace.title = red.name;

			icono.className = 'nb-redes-oficiales__icono';
			icono.textContent = red.short || red.name.substring( 0, 2 );
			icono.setAttribute( 'aria-hidden', 'true' );
			enlace.appendChild( icono );
			lista.appendChild( enlace );
		} );

		contenedor.appendChild( etiqueta );
		contenedor.appendChild( lista );
		return contenedor;
	}

	function simplificarCabecera() {
		var oficiales = redesOficiales();
		var sociales = enlacesSocialesAntesDelMenu();

		if ( ! oficiales.length || ! sociales.length ) {
			return false;
		}

		var franja = encontrarFranja( sociales );

		if ( ! franja ) {
			return false;
		}

		franja.classList.add( 'nb-topbar-social-only' );

		var bloqueRedes = encontrarBloqueRedes( franja, sociales );

		Array.prototype.forEach.call(
			franja.querySelectorAll( '.widget, [class*="col-"], [class*="header-widget"], [class*="top-widget"]' ),
			function ( elemento ) {
				if ( bloqueRedes && ( elemento === bloqueRedes || elemento.contains( bloqueRedes ) || bloqueRedes.contains( elemento ) ) ) {
					elemento.classList.add( 'nb-topbar-redes' );
				} else {
					elemento.classList.add( 'nb-topbar-ocultar' );
				}
			}
		);

		if ( bloqueRedes && ! bloqueRedes.querySelector( '.nb-redes-oficiales' ) ) {
			bloqueRedes.innerHTML = '';
			bloqueRedes.appendChild( crearRedesOficiales() );
		}

		Array.prototype.forEach.call(
			franja.querySelectorAll( '[class*="clock"], [class*="time"], [class*="date"]' ),
			function ( elemento ) {
				if ( ! ( bloqueRedes && bloqueRedes.contains( elemento ) ) ) {
					elemento.classList.add( 'nb-topbar-ocultar' );
				}
			}
		);

		return !! bloqueRedes;
	}

	function iniciar() {
		if ( simplificarCabecera() ) {
			return;
		}

		var intentos = 0;
		var temporizador = window.setInterval( function () {
			intentos += 1;

			if ( simplificarCabecera() || intentos >= 30 ) {
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
