/* CF7 Multi-Step & Save Progress – vanilla JS, no build. */
( function () {
	'use strict';

	var cfg = window.cf7msConfig || {};
	var i18n = cfg.i18n || {};
	var PARAM = 'cf7s';
	var EXCLUDE = '.hidden-fields-container, .wpcf7-response-output, .screen-reader-response';

	function el( tag, cls, text ) {
		var n = document.createElement( tag );
		if ( cls ) { n.className = cls; }
		if ( text ) { n.textContent = text; }
		return n;
	}

	function fmt( s, a, b ) {
		return s.replace( '%1$s', a ).replace( '%2$s', b );
	}

	function getParam() {
		return new URLSearchParams( window.location.search ).get( PARAM ) || '';
	}

	function setParam( key ) {
		var u = new URL( window.location.href );
		if ( key ) { u.searchParams.set( PARAM, key ); } else { u.searchParams.delete( PARAM ); }
		window.history.replaceState( null, '', u.toString() );
	}

	function CF7MS( form ) {
		this.form = form;
		this.formId = ( form.querySelector( 'input[name="_wpcf7"]' ) || {} ).value || '';
		this.key = '';
		this.step = 0;
		this.steps = [];
		this.saveBox = form.querySelector( '.cf7ms-save' );
		this.buildSteps();
		this.bind();
		this.resume();
	}

	CF7MS.prototype.buildSteps = function () {
		var form = this.form, self = this;
		var markers = Array.prototype.slice.call( form.querySelectorAll( '.cf7ms-marker' ) );
		if ( ! markers.length ) { return; }

		// Lowest ancestor containing every marker; its children are the units we split on.
		var root = markers[ 0 ].parentElement;
		while ( root !== form && ! markers.every( function ( m ) { return root.contains( m ); } ) ) {
			root = root.parentElement;
		}
		var unitOf = function ( m ) {
			while ( m.parentElement !== root ) { m = m.parentElement; }
			return m;
		};

		var groups = [], current = { title: '', nodes: [] };
		Array.prototype.slice.call( root.children ).forEach( function ( child ) {
			if ( child.matches( EXCLUDE ) ) { return; }
			var m = child.matches( '.cf7ms-marker' ) ? child : child.querySelector( '.cf7ms-marker' );
			if ( m && unitOf( m ) === child ) {
				// Content before the first marker joins the first step.
				if ( groups.length || ! current.nodes.length ) { current = { title: '', nodes: [] }; groups.push( current ); }
				else { groups.push( current ); }
				current.title = m.getAttribute( 'data-title' ) || '';
			}
			current.nodes.push( child );
		} );
		if ( ! groups.length ) { return; }

		// Always-visible pieces live outside the steps.
		var slot = form.querySelector( '.cf7ms-progress-slot' );
		if ( slot ) { slot.remove(); }
		if ( this.saveBox ) { this.saveBox.remove(); }

		groups.forEach( function ( g, i ) {
			var fs = el( 'fieldset', 'cf7ms-step' );
			g.nodes[ 0 ].parentNode.insertBefore( fs, g.nodes[ 0 ] );
			if ( g.title ) { fs.appendChild( el( 'legend', 'cf7ms-legend', g.title ) ); }
			g.nodes.forEach( function ( n ) { fs.appendChild( n ); } );
			var err = el( 'div', 'cf7ms-step-error' );
			err.setAttribute( 'role', 'alert' );
			fs.appendChild( err );
			var nav = el( 'div', 'cf7ms-nav' );
			if ( i > 0 ) {
				var b = el( 'button', 'cf7ms-prev', i18n.prev || 'Back' );
				b.type = 'button';
				b.addEventListener( 'click', function () { self.show( self.step - 1 ); } );
				nav.appendChild( b );
			}
			if ( i < groups.length - 1 ) {
				var n = el( 'button', 'cf7ms-next', i18n.next || 'Next' );
				n.type = 'button';
				n.addEventListener( 'click', function () { self.next(); } );
				nav.appendChild( n );
			}
			fs.appendChild( nav );
			self.steps.push( { el: fs, title: g.title } );
		} );

		this.progress = el( 'ol', 'cf7ms-progress' );
		this.steps.forEach( function ( s, i ) {
			this.progress.appendChild( el( 'li', '', s.title || fmt( i18n.stepOf || '%1$s/%2$s', i + 1, this.steps.length ) ) );
		}, this );
		var first = this.steps[ 0 ].el, last = this.steps[ this.steps.length - 1 ].el;
		first.parentNode.insertBefore( this.progress, first );
		if ( this.saveBox ) { last.parentNode.insertBefore( this.saveBox, last.nextSibling ); }
		form.classList.add( 'cf7ms-active' );
		this.show( 0, true );
	};

	CF7MS.prototype.show = function ( i, noScroll ) {
		if ( ! this.steps.length ) { return; }
		i = Math.max( 0, Math.min( i, this.steps.length - 1 ) );
		this.step = i;
		this.steps.forEach( function ( s, n ) {
			s.el.hidden = n !== i;
			s.el.classList.toggle( 'is-active', n === i );
		} );
		Array.prototype.forEach.call( this.progress.children, function ( li, n ) {
			li.classList.toggle( 'is-current', n === i );
			li.classList.toggle( 'is-done', n < i );
			if ( n === i ) { li.setAttribute( 'aria-current', 'step' ); li.setAttribute( 'title', fmt( i18n.stepOf || '', i + 1, this.steps.length ) ); }
			else { li.removeAttribute( 'aria-current' ); }
		}, this );
		if ( ! noScroll ) { this.progress.scrollIntoView( { block: 'nearest', behavior: 'smooth' } ); }
	};

	CF7MS.prototype.validateStep = function ( i ) {
		var step = this.steps[ i ].el, bad = [], seen = {};
		step.querySelectorAll( 'input, select, textarea' ).forEach( function ( f ) {
			if ( f.disabled || f.type === 'hidden' || f.type === 'submit' || f.type === 'button' ) { return; }
			f.classList.remove( 'cf7ms-invalid' );
			var group = f.type === 'radio' || f.type === 'checkbox';
			var required = f.required || f.getAttribute( 'aria-required' ) === 'true' || !! f.closest( '.wpcf7-validates-as-required' );
			var ok = true;
			if ( group ) {
				if ( required && ! seen[ f.name ] ) {
					seen[ f.name ] = true;
					var same = step.querySelectorAll( 'input[name="' + f.name.replace( /"/g, '\\"' ) + '"]' );
					ok = Array.prototype.some.call( same, function ( x ) { return x.checked; } );
				}
			} else {
				if ( required && ! String( f.value ).trim() ) { ok = false; }
				else if ( f.value && f.willValidate && ! f.checkValidity() ) { ok = false; }
			}
			if ( ! ok ) { f.classList.add( 'cf7ms-invalid' ); bad.push( f ); }
		} );
		var msg = step.querySelector( '.cf7ms-step-error' );
		msg.textContent = bad.length ? ( i18n.required || '' ) : '';
		if ( bad.length ) { bad[ 0 ].focus(); }
		return ! bad.length;
	};

	CF7MS.prototype.next = function () {
		if ( this.validateStep( this.step ) ) {
			this.show( this.step + 1 );
			this.queueAutosave( true );
		}
	};

	CF7MS.prototype.collect = function () {
		var data = {};
		Array.prototype.forEach.call( this.form.elements, function ( f ) {
			if ( ! f.name || f.name.charAt( 0 ) === '_' || f.disabled ) { return; }
			if ( [ 'file', 'password', 'submit', 'button', 'reset' ].indexOf( f.type ) > -1 ) { return; }
			var multi = /\[\]$/.test( f.name ), name = f.name.replace( /\[\]$/, '' );
			if ( f.type === 'radio' || f.type === 'checkbox' ) {
				if ( ! f.checked ) { return; }
				if ( multi ) { ( data[ name ] = data[ name ] || [] ).push( f.value ); }
				else { data[ name ] = f.value; }
			} else if ( f.type === 'select-multiple' ) {
				data[ name ] = Array.prototype.filter.call( f.options, function ( o ) { return o.selected; } ).map( function ( o ) { return o.value; } );
			} else {
				data[ name ] = f.value;
			}
		} );
		return data;
	};

	CF7MS.prototype.restore = function ( data ) {
		var form = this.form;
		Object.keys( data || {} ).forEach( function ( name ) {
			var vals = [].concat( data[ name ] ).map( String );
			var esc = window.CSS && CSS.escape ? CSS.escape( name ) : name;
			form.querySelectorAll( '[name="' + esc + '"], [name="' + esc + '[]"]' ).forEach( function ( f ) {
				if ( f.type === 'radio' || f.type === 'checkbox' ) { f.checked = vals.indexOf( f.value ) > -1; }
				else if ( f.type === 'select-multiple' ) {
					Array.prototype.forEach.call( f.options, function ( o ) { o.selected = vals.indexOf( o.value ) > -1; } );
				} else if ( f.type !== 'file' ) { f.value = vals[ 0 ] || ''; }
				f.dispatchEvent( new Event( 'input', { bubbles: true } ) );
				f.dispatchEvent( new Event( 'change', { bubbles: true } ) );
			} );
		} );
	};

	CF7MS.prototype.notice = function ( text ) {
		if ( ! this.noticeEl ) {
			this.noticeEl = el( 'div', 'cf7ms-notice' );
			this.noticeEl.setAttribute( 'role', 'status' );
			this.form.insertBefore( this.noticeEl, this.form.firstChild );
		}
		this.noticeEl.textContent = text;
	};

	CF7MS.prototype.setKey = function ( key ) {
		this.key = key;
		var h = this.form.querySelector( 'input[name="_cf7ms_key"]' );
		if ( key && ! h ) {
			h = el( 'input' );
			h.type = 'hidden';
			h.name = '_cf7ms_key';
			this.form.appendChild( h );
		}
		if ( h ) { h.value = key; if ( ! key ) { h.remove(); } }
	};

	CF7MS.prototype.resume = function () {
		var key = getParam(), self = this;
		if ( ! key || ! /^[a-f0-9]{32}$/.test( key ) || ! this.formId ) { return; }
		var u = cfg.restUrl + 'load?key=' + encodeURIComponent( key ) + '&form_id=' + encodeURIComponent( this.formId );
		fetch( u, { credentials: 'omit' } ).then( function ( r ) {
			if ( ! r.ok ) { throw new Error( 'nf' ); }
			return r.json();
		} ).then( function ( res ) {
			self.restore( res.data );
			self.setKey( key );
			self.show( res.step || 0, true );
			self.notice( i18n.restored || '' );
		} ).catch( function () {
			if ( self.saveBox ) { self.notice( i18n.notFound || '' ); }
		} );
	};

	CF7MS.prototype.save = function ( silent ) {
		var self = this, body = {
			form_id: this.formId,
			key: this.key,
			data: this.collect(),
			step: this.step,
			url: window.location.href
		};
		var emailInput = this.saveBox && this.saveBox.querySelector( '.cf7ms-save-email input' );
		if ( emailInput && emailInput.value && ! silent ) { body.email = emailInput.value; }
		return fetch( cfg.restUrl + 'save', {
			method: 'POST',
			credentials: 'omit',
			headers: { 'Content-Type': 'application/json' },
			body: JSON.stringify( body )
		} ).then( function ( r ) {
			if ( ! r.ok ) { throw new Error( 'save' ); }
			return r.json();
		} ).then( function ( res ) {
			self.setKey( res.key );
			setParam( res.key );
			if ( ! silent ) { self.showLink( res ); }
		} ).catch( function () {
			if ( ! silent && self.saveBox ) {
				self.saveBox.querySelector( '.cf7ms-save-out' ).textContent = i18n.error || '';
			}
		} );
	};

	CF7MS.prototype.showLink = function ( res ) {
		var out = this.saveBox.querySelector( '.cf7ms-save-out' );
		out.textContent = '';
		out.appendChild( el( 'p', '', ( i18n.saved || '' ) + ( res.emailed ? ' ' + ( i18n.emailSent || '' ) : '' ) ) );
		var input = el( 'input', 'cf7ms-link' );
		input.type = 'text';
		input.readOnly = true;
		input.value = res.link;
		input.addEventListener( 'focus', function () { input.select(); } );
		var copy = el( 'button', 'cf7ms-copy', i18n.copy || 'Copy link' );
		copy.type = 'button';
		copy.addEventListener( 'click', function () {
			input.select();
			var done = function () { copy.textContent = i18n.copied || 'Copied!'; };
			if ( navigator.clipboard ) { navigator.clipboard.writeText( res.link ).then( done ); }
			else { document.execCommand( 'copy' ); done(); }
		} );
		out.appendChild( input );
		out.appendChild( copy );
	};

	CF7MS.prototype.queueAutosave = function ( immediate ) {
		if ( ! this.saveBox || this.saveBox.getAttribute( 'data-autosave' ) !== '1' ) { return; }
		var self = this;
		clearTimeout( this.timer );
		this.timer = setTimeout( function () { self.save( true ); }, immediate ? 0 : 1500 );
	};

	CF7MS.prototype.bind = function () {
		var self = this, form = this.form;

		if ( this.saveBox ) {
			if ( this.saveBox.getAttribute( 'data-email' ) === '1' ) {
				var wrap = el( 'label', 'cf7ms-save-email' );
				wrap.appendChild( el( 'span', '', i18n.emailLbl || 'Email' ) );
				var inp = el( 'input' );
				inp.type = 'email';
				inp.autocomplete = 'email';
				wrap.appendChild( inp );
				this.saveBox.insertBefore( wrap, this.saveBox.firstChild );
			}
			this.saveBox.querySelector( '.cf7ms-save-btn' ).addEventListener( 'click', function () { self.save( false ); } );
			form.addEventListener( 'input', function ( e ) {
				if ( ! e.target.closest( '.cf7ms-save-email' ) ) { self.queueAutosave( false ); }
			} );
		}

		form.addEventListener( 'keydown', function ( e ) {
			if ( e.key !== 'Enter' || ! self.steps.length || self.step >= self.steps.length - 1 ) { return; }
			var t = e.target;
			if ( t.tagName === 'INPUT' && [ 'submit', 'button' ].indexOf( t.type ) === -1 ) {
				e.preventDefault();
				self.next();
			}
		} );

		form.closest( '.wpcf7' ).addEventListener( 'wpcf7invalid', function () {
			var bad = form.querySelector( '.wpcf7-not-valid' );
			if ( ! bad ) { return; }
			self.steps.some( function ( s, i ) {
				if ( s.el.contains( bad ) ) { self.show( i, true ); bad.focus(); return true; }
				return false;
			} );
		} );

		form.closest( '.wpcf7' ).addEventListener( 'wpcf7mailsent', function () {
			self.setKey( '' );
			setParam( '' );
			if ( self.noticeEl ) { self.noticeEl.textContent = ''; }
			self.show( 0, true );
		} );
	};

	function init() {
		document.querySelectorAll( '.wpcf7 form.wpcf7-form' ).forEach( function ( f ) {
			if ( f.__cf7ms || ! f.querySelector( '.cf7ms-marker, .cf7ms-save' ) ) { return; }
			f.__cf7ms = new CF7MS( f );
		} );
	}

	if ( document.readyState === 'loading' ) { document.addEventListener( 'DOMContentLoaded', init ); }
	else { init(); }
} )();
