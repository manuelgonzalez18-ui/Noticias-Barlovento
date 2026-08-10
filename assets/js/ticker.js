/*
 * Ticker autónomo de Noticias Barlovento.
 * Reconstruye la franja completa dentro de .trending-news-area para no
 * depender del HTML ni de las animaciones internas de NewsExo.
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

	function crearGrupo( items, duplicado ) {
		var grupo = document.createElement( 'div' );
		grupo.className = 'nb-ticker-grupo';

		if ( duplicado ) {
			grupo.setAttribute( 'aria-hidden', 'true' );
		}

		items.forEach( function ( item ) {
			var enlace = document.createElement( 'a' );
			var texto = document.createElement( 'span' );

			enlace.className = 'nb-ticker-enlace';
			enlace.href = item.url;
			texto.textContent = item.title;
			enlace.appendChild( texto );

			if ( duplicado ) {
				enlace.setAttribute( 'tabindex', '-1' );
			}

			grupo.appendChild( enlace );
		} );

		return grupo;
	}

	function encontrarArea() {
		var area = document.querySelector( '.trending-news-area' );

		if ( area ) {
			return area;
		}

		/* Respaldo si el tema cambia el nombre de la clase en una actualización. */
		var candidatos = Array.prototype.slice.call( document.querySelectorAll( 'section, div' ) );

		return candidatos.find( function ( elemento ) {
			var texto = ( elemento.textContent || '' ).replace( /\s+/g, ' ' ).trim().toLowerCase();
			return texto.indexOf( 'puedes haberte perdido' ) === 0 && elemento.getBoundingClientRect().height < 180;
		} ) || null;
	}

	function montarTicker() {
		var area = encontrarArea();
		var items = obtenerItems();

		if ( ! area || items.length < 2 ) {
			return false;
		}

		if ( area.dataset.nbTicker === 'autonomo' ) {
			return true;
		}

		var barra = document.createElement( 'div' );
		var etiqueta = document.createElement( 'div' );
		var viewport = document.createElement( 'div' );
		var track = document.createElement( 'div' );
		var textoEtiqueta = document.createElement( 'span' );
		var icono = document.createElement( 'span' );

		barra.className = 'nb-ticker-barra';
		etiqueta.className = 'nb-ticker-etiqueta';
		viewport.className = 'nb-ticker-viewport';
		track.className = 'nb-ticker-track';
		textoEtiqueta.textContent = 'PUEDES HABERTE PERDIDO';
		icono.className = 'nb-ticker-etiqueta__icono';
		icono.textContent = '📣';
		icono.setAttribute( 'aria-hidden', 'true' );

		etiqueta.appendChild( textoEtiqueta );
		etiqueta.appendChild( icono );
		track.appendChild( crearGrupo( items, false ) );
		track.appendChild( crearGrupo( items, true ) );
		viewport.appendChild( track );
		barra.appendChild( etiqueta );
		barra.appendChild( viewport );

		area.innerHTML = '';
		area.classList.add( 'nb-ticker-autonomo' );
		area.dataset.nbTicker = 'autonomo';
		area.appendChild( barra );

		return true;
	}

	function iniciar() {
		if ( montarTicker() ) {
			return;
		}

		var intentos = 0;
		var temporizador = window.setInterval( function () {
			intentos += 1;

			if ( montarTicker() || intentos >= 40 ) {
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
