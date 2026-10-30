/**
 * Parallax Hero Carousel - Lightweight WebGL Depth Displacement Engine
 *
 * Zero external dependencies (< 5KB), hardware-accelerated 2.5D depth parallax.
 *
 * Features:
 * - Desktop: 2:1 aspect ratio, subtle cursor-following 3D perspective shift.
 * - Mobile (<= 600px): 1:1 aspect ratio, right-anchored crop, horizontal scroll-driven displacement.
 * - Carousel navigation dots in lower-right corner.
 * - CPU/GPU efficient: only animates when visible in viewport and moving.
 */

(function () {
	'use strict';

	/* ==========================================================================
	   CONFIGURABLE ADJUSTMENT VALUES
	   Developers and designers can modify these parameters to tune the visual effect.
	   ========================================================================== */
	var CONFIG = {
		// --- Desktop Cursor Parallax ---
		// Intensity of the 3D perspective shift on mouseover.
		// Higher values = deeper, more dramatic shift; lower = more subtle.
		MOUSE_INTENSITY_X: 0.035, // Recommended: 0.015 - 0.060
		MOUSE_INTENSITY_Y: 0.020, // Recommended: 0.010 - 0.035

		// --- Mobile Scroll-Driven Parallax ---
		// Intensity of horizontal displacement triggered by vertical scroll on mobile.
		SCROLL_INTENSITY_X: 0.045, // Recommended: 0.025 - 0.080

		// --- Responsive Breakpoint (px) ---
		// Viewports at or below this width switch to 1:1 aspect ratio,
		// right-anchored cropping, and scroll-driven horizontal displacement.
		MOBILE_BREAKPOINT: 600,

		// --- Motion Smoothing (Lerp Damping) ---
		// 0.01 = very slow/heavy inertia, 0.08 = smooth & fluid, 0.25 = snappy.
		DAMPING: 0.08,

		// --- Texture Overscan Margin (Zoom) ---
		// Slightly scales texture inward so parallax displacement never reveals
		// raw edges or causes border clamp stretching. (1.05 = 5% margin)
		OVERSCAN: 1.05,

		// --- Focal Depth Plane ---
		// Depth value (0.0 to 1.0) that remains stationary. Objects closer than
		// this move in one direction, while objects farther away move opposite.
		FOCAL_DEPTH: 0.5,
	};

	/* ==========================================================================
	   WebGL Shaders
	   ========================================================================== */
	var VERTEX_SHADER_SOURCE = [
		'attribute vec2 a_position;',
		'varying vec2 v_uv;',
		'void main() {',
		'    // Map clip space [-1, 1] to UV [0, 1] (WebGL Y is flipped relative to DOM images)',
		'    v_uv = vec2((a_position.x + 1.0) * 0.5, (1.0 - a_position.y) * 0.5);',
		'    gl_Position = vec4(a_position, 0.0, 1.0);',
		'}'
	].join('\n');

	var FRAGMENT_SHADER_SOURCE = [
		'precision mediump float;',
		'uniform sampler2D u_image;',
		'uniform sampler2D u_depth;',
		'uniform vec2 u_offset;',
		'uniform vec2 u_resolution;',
		'uniform vec2 u_image_size;',
		'uniform float u_anchor_x;',
		'uniform float u_overscan;',
		'uniform float u_focal_depth;',
		'varying vec2 v_uv;',
		'void main() {',
		'    // 1. Calculate aspect-ratio cover UVs',
		'    float canvasAspect = u_resolution.x / u_resolution.y;',
		'    float imageAspect = u_image_size.x / u_image_size.y;',
		'    vec2 coverUv = v_uv;',
		'    if (canvasAspect < imageAspect) {',
		'        // Container is narrower than image (e.g. mobile 1:1): crop sides based on anchor',
		'        float scaleX = canvasAspect / imageAspect;',
		'        coverUv.x = (1.0 - scaleX) * u_anchor_x + v_uv.x * scaleX;',
		'    } else {',
		'        // Container is wider than image: crop top and bottom centered',
		'        float scaleY = imageAspect / canvasAspect;',
		'        coverUv.y = (1.0 - scaleY) * 0.5 + v_uv.y * scaleY;',
		'    }',
		'    // 2. Pre-zoom texture to create bleed margin for parallax travel',
		'    vec2 centeredUv = (coverUv - 0.5) / u_overscan + 0.5;',
		'    // 3. Sample depth map using perceptual luminance (supports grayscale, RGB, and custom depth maps)',
		'    vec4 depthColor = texture2D(u_depth, centeredUv);',
		'    float depth = dot(depthColor.rgb, vec3(0.299, 0.587, 0.114));',
		'    // 4. Calculate displacement relative to focal plane',
		'    vec2 displacement = (depth - u_focal_depth) * u_offset;',
		'    vec2 finalUv = clamp(centeredUv + displacement, 0.001, 0.999);',
		'    gl_FragColor = texture2D(u_image, finalUv);',
		'}'
	].join('\n');

	/* ==========================================================================
	   WebGL Helper Functions
	   ========================================================================== */
	function createShader(gl, type, source) {
		var shader = gl.createShader(type);
		gl.shaderSource(shader, source);
		gl.compileShader(shader);
		if (!gl.getShaderParameter(shader, gl.COMPILE_STATUS)) {
			console.error('Parallax Hero shader compile error:', gl.getShaderInfoLog(shader));
			gl.deleteShader(shader);
			return null;
		}
		return shader;
	}

	function createProgram(gl, vertexShader, fragmentShader) {
		var program = gl.createProgram();
		gl.attachShader(program, vertexShader);
		gl.attachShader(program, fragmentShader);
		gl.linkProgram(program);
		if (!gl.getProgramParameter(program, gl.LINK_STATUS)) {
			console.error('Parallax Hero program link error:', gl.getProgramInfoLog(program));
			gl.deleteProgram(program);
			return null;
		}
		return program;
	}

	function createTexture(gl) {
		var texture = gl.createTexture();
		gl.bindTexture(gl.TEXTURE_2D, texture);
		// Default 1x1 placeholder pixel until image loads
		gl.texImage2D(gl.TEXTURE_2D, 0, gl.RGBA, 1, 1, 0, gl.RGBA, gl.UNSIGNED_BYTE, new Uint8Array([128, 128, 128, 255]));
		gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_S, gl.CLAMP_TO_EDGE);
		gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_T, gl.CLAMP_TO_EDGE);
		gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MIN_FILTER, gl.LINEAR);
		gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MAG_FILTER, gl.LINEAR);
		return texture;
	}

	function updateTexture(gl, texture, image) {
		gl.bindTexture(gl.TEXTURE_2D, texture);
		gl.pixelStorei(gl.UNPACK_FLIP_Y_WEBGL, false);
		gl.texImage2D(gl.TEXTURE_2D, 0, gl.RGBA, gl.RGBA, gl.UNSIGNED_BYTE, image);
	}

	/* ==========================================================================
	   Slide WebGL Instance
	   ========================================================================== */
	function ParallaxSlide(slideEl) {
		this.slideEl = slideEl;
		this.canvas = slideEl.querySelector('.parallax-hero-canvas');
		this.imageUrl = slideEl.getAttribute('data-image');
		this.depthUrl = slideEl.getAttribute('data-depth');
		this.isLoaded = false;
		this.isActive = slideEl.classList.contains('is-active');

		this.image = null;
		this.depthImage = null;
		this.imageWidth = 1920;
		this.imageHeight = 960;

		this.initWebGL();
		this.loadAssets();
	}

	ParallaxSlide.prototype.initWebGL = function () {
		if (!this.canvas) return;
		var gl = this.canvas.getContext('webgl', { alpha: false, antialias: false, depth: false }) ||
		         this.canvas.getContext('experimental-webgl', { alpha: false, antialias: false, depth: false });

		if (!gl) {
			console.warn('Parallax Hero: WebGL not supported, falling back to static presentation.');
			return;
		}
		this.gl = gl;

		var vert = createShader(gl, gl.VERTEX_SHADER, VERTEX_SHADER_SOURCE);
		var frag = createShader(gl, gl.FRAGMENT_SHADER, FRAGMENT_SHADER_SOURCE);
		if (!vert || !frag) return;

		var program = createProgram(gl, vert, frag);
		if (!program) return;
		this.program = program;

		// Locations
		this.posLoc = gl.getAttribLocation(program, 'a_position');
		this.imageLoc = gl.getUniformLocation(program, 'u_image');
		this.depthLoc = gl.getUniformLocation(program, 'u_depth');
		this.offsetLoc = gl.getUniformLocation(program, 'u_offset');
		this.resLoc = gl.getUniformLocation(program, 'u_resolution');
		this.imgSizeLoc = gl.getUniformLocation(program, 'u_image_size');
		this.anchorLoc = gl.getUniformLocation(program, 'u_anchor_x');
		this.overscanLoc = gl.getUniformLocation(program, 'u_overscan');
		this.focalLoc = gl.getUniformLocation(program, 'u_focal_depth');

		// Full-screen quad buffer
		var positionBuffer = gl.createBuffer();
		gl.bindBuffer(gl.ARRAY_BUFFER, positionBuffer);
		gl.bufferData(
			gl.ARRAY_BUFFER,
			new Float32Array([
				-1, -1,
				 1, -1,
				-1,  1,
				-1,  1,
				 1, -1,
				 1,  1,
			]),
			gl.STATIC_DRAW
		);
		this.positionBuffer = positionBuffer;

		// Textures
		this.imageTexture = createTexture(gl);
		this.depthTexture = createTexture(gl);
	};

	ParallaxSlide.prototype.loadAssets = function () {
		var self = this;
		var loadedCount = 0;
		var totalNeeded = self.depthUrl ? 2 : 1;

		function checkComplete() {
			loadedCount++;
			if (loadedCount >= totalNeeded) {
				self.isLoaded = true;
				if (self.gl) {
					if (self.image) {
						updateTexture(self.gl, self.imageTexture, self.image);
						self.imageWidth = self.image.naturalWidth || self.image.width || 1920;
						self.imageHeight = self.image.naturalHeight || self.image.height || 960;
					}
					if (self.depthImage) {
						updateTexture(self.gl, self.depthTexture, self.depthImage);
					}
				}
				self.render(0, 0);
			}
		}

		if (self.imageUrl) {
			var img = new Image();
			img.crossOrigin = 'anonymous';
			img.onload = function () {
				self.image = img;
				checkComplete();
			};
			img.onerror = function () {
				console.error('Parallax Hero: Failed to load image:', self.imageUrl);
				checkComplete();
			};
			img.src = self.imageUrl;
		}

		if (self.depthUrl) {
			var depthImg = new Image();
			depthImg.crossOrigin = 'anonymous';
			depthImg.onload = function () {
				self.depthImage = depthImg;
				checkComplete();
			};
			depthImg.onerror = function () {
				console.warn('Parallax Hero: Failed to load depth map:', self.depthUrl);
				checkComplete();
			};
			depthImg.src = self.depthUrl;
		}
	};

	ParallaxSlide.prototype.resize = function () {
		if (!this.gl || !this.canvas) return;
		var gl = this.gl;
		var dpr = Math.min(window.devicePixelRatio || 1, 2);
		var rect = this.canvas.getBoundingClientRect();
		var displayWidth = Math.round(rect.width * dpr);
		var displayHeight = Math.round(rect.height * dpr);

		if (this.canvas.width !== displayWidth || this.canvas.height !== displayHeight) {
			this.canvas.width = displayWidth || 1;
			this.canvas.height = displayHeight || 1;
			gl.viewport(0, 0, this.canvas.width, this.canvas.height);
		}
	};

	ParallaxSlide.prototype.render = function (offsetX, offsetY) {
		if (!this.gl || !this.isLoaded || !this.program) return;
		var gl = this.gl;

		this.resize();

		gl.useProgram(this.program);

		// Position attribute
		gl.bindBuffer(gl.ARRAY_BUFFER, this.positionBuffer);
		gl.enableVertexAttribArray(this.posLoc);
		gl.vertexAttribPointer(this.posLoc, 2, gl.FLOAT, false, 0, 0);

		// Bind textures
		gl.activeTexture(gl.TEXTURE0);
		gl.bindTexture(gl.TEXTURE_2D, this.imageTexture);
		gl.uniform1i(this.imageLoc, 0);

		gl.activeTexture(gl.TEXTURE1);
		gl.bindTexture(gl.TEXTURE_2D, this.depthTexture);
		gl.uniform1i(this.depthLoc, 1);

		// Uniforms
		gl.uniform2f(this.offsetLoc, offsetX, offsetY);
		gl.uniform2f(this.resLoc, this.canvas.width, this.canvas.height);
		gl.uniform2f(this.imgSizeLoc, this.imageWidth, this.imageHeight);

		// Horizontal anchor: 1.0 = right-anchored on mobile (<= 600px), 0.5 = centered on desktop
		var isMobile = window.innerWidth <= CONFIG.MOBILE_BREAKPOINT;
		var anchorX = isMobile ? 1.0 : 0.5;
		gl.uniform1f(this.anchorLoc, anchorX);

		gl.uniform1f(this.overscanLoc, CONFIG.OVERSCAN);
		gl.uniform1f(this.focalLoc, CONFIG.FOCAL_DEPTH);

		// Draw
		gl.drawArrays(gl.TRIANGLES, 0, 6);
	};

	/* ==========================================================================
	   Carousel Controller
	   ========================================================================== */
	function ParallaxCarousel(container) {
		this.container = container;
		this.slides = [];
		this.currentIndex = 0;
		this.targetX = 0;
		this.targetY = 0;
		this.currentX = 0;
		this.currentY = 0;
		this.animFrameId = null;
		this.isInView = false;
		this.isHovered = false;

		var slideEls = container.querySelectorAll('.parallax-hero-slide');
		for (var i = 0; i < slideEls.length; i++) {
			this.slides.push(new ParallaxSlide(slideEls[i]));
		}

		this.dots = container.querySelectorAll('.parallax-hero-dot');
		this.contentPanels = container.querySelectorAll('.parallax-hero-slide-content');

		this.bindEvents();
		this.setupIntersectionObserver();
		this.updateSlide(0);
		this.startAnimation();
	}

	ParallaxCarousel.prototype.bindEvents = function () {
		var self = this;

		// --- Desktop Mouse Interaction ---
		self.container.addEventListener('mousemove', function (e) {
			if (window.innerWidth <= CONFIG.MOBILE_BREAKPOINT) return;
			self.isHovered = true;
			var rect = self.container.getBoundingClientRect();
			// Normalized mouse position: -0.5 to +0.5 from center
			var nx = (e.clientX - rect.left) / rect.width - 0.5;
			var ny = (e.clientY - rect.top) / rect.height - 0.5;

			self.targetX = nx * CONFIG.MOUSE_INTENSITY_X;
			self.targetY = ny * CONFIG.MOUSE_INTENSITY_Y;
		});

		self.container.addEventListener('mouseleave', function () {
			self.isHovered = false;
			self.targetX = 0;
			self.targetY = 0;
		});

		// --- Mobile Scroll Interaction ---
		var onScroll = function () {
			if (window.innerWidth > CONFIG.MOBILE_BREAKPOINT) return;
			var rect = self.container.getBoundingClientRect();
			var winHeight = window.innerHeight;

			// Slide vertical position relative to viewport center
			var slideCenter = rect.top + rect.height * 0.5;
			var viewportCenter = winHeight * 0.5;
			var totalSpan = (winHeight + rect.height) * 0.5;
			var progress = (viewportCenter - slideCenter) / totalSpan;
			progress = Math.max(-1, Math.min(1, progress));

			// Horizontal displacement on mobile as user scrolls
			self.targetX = progress * CONFIG.SCROLL_INTENSITY_X;
			self.targetY = 0;
		};

		window.addEventListener('scroll', onScroll, { passive: true });

		// --- Resize Handling ---
		window.addEventListener('resize', function () {
			var activeSlide = self.slides[self.currentIndex];
			if (activeSlide) {
				activeSlide.resize();
				activeSlide.render(self.currentX, self.currentY);
			}
			if (window.innerWidth <= CONFIG.MOBILE_BREAKPOINT) {
				onScroll();
			} else if (!self.isHovered) {
				self.targetX = 0;
				self.targetY = 0;
			}
		});

		// --- Navigation Dots (Lower-Right Corner) ---
		for (var d = 0; d < self.dots.length; d++) {
			(function (index) {
				self.dots[index].addEventListener('click', function () {
					self.updateSlide(index);
				});
			})(d);
		}

		// Initial evaluation for mobile on load
		if (window.innerWidth <= CONFIG.MOBILE_BREAKPOINT) {
			onScroll();
		}
	};

	ParallaxCarousel.prototype.setupIntersectionObserver = function () {
		var self = this;
		if ('IntersectionObserver' in window) {
			var observer = new IntersectionObserver(function (entries) {
				entries.forEach(function (entry) {
					self.isInView = entry.isIntersecting;
					if (self.isInView) {
						var activeSlide = self.slides[self.currentIndex];
						if (activeSlide) {
							activeSlide.render(self.currentX, self.currentY);
						}
					}
				});
			}, { threshold: 0.05 });
			observer.observe(self.container);
		} else {
			self.isInView = true;
		}
	};

	ParallaxCarousel.prototype.updateSlide = function (newIndex) {
		if (newIndex < 0 || newIndex >= this.slides.length) return;
		this.currentIndex = newIndex;

		for (var i = 0; i < this.slides.length; i++) {
			var isCurrent = (i === newIndex);
			this.slides[i].isActive = isCurrent;
			this.slides[i].slideEl.classList.toggle('is-active', isCurrent);
			if (this.dots[i]) {
				this.dots[i].classList.toggle('is-active', isCurrent);
				this.dots[i].setAttribute('aria-selected', isCurrent ? 'true' : 'false');
			}
		}

		// Advance inner block content panels with the active slide
		for (var c = 0; c < this.contentPanels.length; c++) {
			var isCurrentContent = (c === newIndex);
			this.contentPanels[c].classList.toggle('is-active', isCurrentContent);
		}

		var activeSlide = this.slides[this.currentIndex];
		if (activeSlide) {
			activeSlide.render(this.currentX, this.currentY);
		}
	};

	ParallaxCarousel.prototype.startAnimation = function () {
		var self = this;

		function tick() {
			// Lerp current offset toward target offset
			var dx = self.targetX - self.currentX;
			var dy = self.targetY - self.currentY;

			if (Math.abs(dx) > 0.00005 || Math.abs(dy) > 0.00005) {
				self.currentX += dx * CONFIG.DAMPING;
				self.currentY += dy * CONFIG.DAMPING;

				if (self.isInView) {
					var activeSlide = self.slides[self.currentIndex];
					if (activeSlide && activeSlide.isLoaded) {
						activeSlide.render(self.currentX, self.currentY);
					}
				}
			}

			self.animFrameId = requestAnimationFrame(tick);
		}

		this.animFrameId = requestAnimationFrame(tick);
	};

	/* ==========================================================================
	   Auto-Initialization
	   ========================================================================== */
	function init() {
		var carousels = document.querySelectorAll('.parallax-hero-carousel');
		for (var i = 0; i < carousels.length; i++) {
			if (!carousels[i].parallaxHeroInitialized) {
				carousels[i].parallaxHeroInitialized = true;
				new ParallaxCarousel(carousels[i]);
			}
		}
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
