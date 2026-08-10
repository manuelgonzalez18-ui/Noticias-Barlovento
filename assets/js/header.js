/*
 * Convierte la franja superior heredada de NewsExo en un espacio publicitario.
 * Las redes oficiales permanecen exclusivamente en la sección de contacto.
 */
( function () {
	'use strict';

	var dominiosSociales = [
		'facebook.com', 'fb.com', 'twitter.com', 'x.com', 'instagram.com',
		'linkedin.com', 'youtube.com', 'youtu.be', 'tiktok.com', 't.me',
		'telegram.me', 'threads.net', 'bsky.app', 'wa.me', 'whatsapp.com'
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

			if ( rect.height > 240 || rect.bottom > limiteMenu ) {
				break;
			}

			mejor = padre;
			actual = padre;
		}

		return mejor;
	}

	function crearPublicidad() {
		var contenedor = document.createElement( 'div' );
		var enlace = document.createElement( 'a' );
		var titulo = document.createElement( 'span' );
		var subtitulo = document.createElement( 'small' );

		contenedor.className = 'nb-publicidad-superior';
		enlace.className = 'nb-publicidad-superior__enlace';
		enlace.href = '/#contacto';
		enlace.setAttribute( 'aria-label', 'Espacio publicitario. Contactar a Noticias Barlovento' );

		titulo.className = 'nb-publicidad-superior__titulo';
		titulo.textContent = 'ESPACIO PUBLICITARIO';
		subtitulo.className = 'nb-publicidad-superior__subtitulo';
		subtitulo.textContent = 'Anúnciate en Noticias Barlovento';

		enlace.appendChild( titulo );
		enlace.appendChild( subtitulo );
		contenedor.appendChild( enlace );

		return contenedor;
	}

	function convertirCabecera() {
		var sociales = enlacesSocialesAntesDelMenu();

		if ( ! sociales.length ) {
			return false;
		}

		var franja = encontrarFranja( sociales );

		if ( ! franja ) {
			return false;
		}

		if ( franja.dataset.nbPublicidad === 'activa' ) {
			return true;
		}

		franja.dataset.nbPublicidad = 'activa';
		franja.classList.remove( 'nb-topbar-social-only' );
		franja.classList.add( 'nb-topbar-publicidad' );
		franja.innerHTML = '';
		franja.appendChild( crearPublicidad() );

		return true;
	}

	function iniciar() {
		if ( convertirCabecera() ) {
			return;
		}

		var intentos = 0;
		var temporizador = window.setInterval( function () {
			intentos += 1;

			if ( convertirCabecera() || intentos >= 30 ) {
				window.clearInterval( temporizador );
			}
		}, 250 );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', iniciar );
	} else {
		iniciar();
	}
}() );
