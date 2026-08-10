/*
 * Convierte la franja superior heredada de NewsExo en un espacio publicitario.
 * La campaña activa promociona Higo.
 */
( function () {
	'use strict';

	var dominiosSociales = [
		'facebook.com', 'fb.com', 'twitter.com', 'x.com', 'instagram.com',
		'linkedin.com', 'youtube.com', 'youtu.be', 'tiktok.com', 't.me',
		'telegram.me', 'threads.net', 'bsky.app', 'wa.me', 'whatsapp.com'
	];

	function datosHigo() {
		if ( window.nbHeaderData && window.nbHeaderData.higo ) {
			return window.nbHeaderData.higo;
		}

		return {
			url: 'https://play.google.com/store/apps/details?id=com.higoapp.ve',
			logo: ''
		};
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

			if ( rect.height > 240 || rect.bottom > limiteMenu ) {
				break;
			}

			mejor = padre;
			actual = padre;
		}

		return mejor;
	}

	function crearPublicidadHigo() {
		var higo = datosHigo();
		var contenedor = document.createElement( 'div' );
		var enlace = document.createElement( 'a' );
		var etiqueta = document.createElement( 'span' );
		var marca = document.createElement( 'div' );
		var texto = document.createElement( 'div' );
		var titulo = document.createElement( 'strong' );
		var mensaje = document.createElement( 'span' );
		var detalle = document.createElement( 'small' );
		var boton = document.createElement( 'span' );

		contenedor.className = 'nb-publicidad-superior nb-publicidad-higo';
		enlace.className = 'nb-publicidad-higo__enlace';
		enlace.href = higo.url || 'https://play.google.com/store/apps/details?id=com.higoapp.ve';
		enlace.target = '_blank';
		enlace.rel = 'noopener noreferrer sponsored';
		enlace.setAttribute( 'aria-label', 'Publicidad de Higo. Muévete y envía con Higo' );

		etiqueta.className = 'nb-publicidad-higo__etiqueta';
		etiqueta.textContent = 'PUBLICIDAD';

		marca.className = 'nb-publicidad-higo__marca';
		if ( higo.logo ) {
			var logo = document.createElement( 'img' );
			logo.className = 'nb-publicidad-higo__logo';
			logo.src = higo.logo;
			logo.alt = '';
			logo.width = 64;
			logo.height = 64;
			marca.appendChild( logo );
		}

		texto.className = 'nb-publicidad-higo__texto';
		titulo.textContent = 'Higo';
		mensaje.textContent = 'Muévete y envía con Higo';
		detalle.textContent = 'Movilidad y envíos en Venezuela';
		texto.appendChild( titulo );
		texto.appendChild( mensaje );
		texto.appendChild( detalle );
		marca.appendChild( texto );

		boton.className = 'nb-publicidad-higo__boton';
		boton.textContent = 'Conoce Higo →';

		enlace.appendChild( etiqueta );
		enlace.appendChild( marca );
		enlace.appendChild( boton );
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

		if ( franja.dataset.nbPublicidad === 'higo' ) {
			return true;
		}

		franja.dataset.nbPublicidad = 'higo';
		franja.classList.remove( 'nb-topbar-social-only' );
		franja.classList.add( 'nb-topbar-publicidad', 'nb-topbar-publicidad--higo' );
		franja.innerHTML = '';
		franja.appendChild( crearPublicidadHigo() );

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
