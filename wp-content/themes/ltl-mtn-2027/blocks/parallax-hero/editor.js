/**
 * Parallax Hero Block - Editor Implementation (No-Build)
 *
 * Implements a repeater block interface for managing carousel slides.
 * Each slide instance contains:
 * - Hero Image
 * - Depth Map
 * - Alt text / Caption
 *
 * Built with vanilla WordPress JavaScript (wp.element.createElement) without compilation.
 */

(function () {
	'use strict';

	var el = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var registerBlockType = wp.blocks.registerBlockType;
	var useBlockProps = wp.blockEditor.useBlockProps;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var MediaUpload = wp.blockEditor.MediaUpload;
	var MediaUploadCheck = wp.blockEditor.MediaUploadCheck;
	var PanelBody = wp.components.PanelBody;
	var Button = wp.components.Button;
	var TextControl = wp.components.TextControl;
	var Notice = wp.components.Notice;
	var __ = wp.i18n.__;

	// Reference media URLs (populated via PHP or default fallback)
	var referenceImage = (window.ltlMtnParallaxHeroData && window.ltlMtnParallaxHeroData.referenceImage) ||
		'/wp-content/themes/ltl-mtn-2027/blocks/parallax-hero/reference/sample-hero.jpg';
	var referenceDepthMap = (window.ltlMtnParallaxHeroData && window.ltlMtnParallaxHeroData.referenceDepthMap) ||
		'/wp-content/themes/ltl-mtn-2027/blocks/parallax-hero/reference/sample-hero.webp';

	registerBlockType('ltl-mtn/parallax-hero', {
		edit: function (props) {
			var attributes = props.attributes;
			var setAttributes = props.setAttributes;
			var slides = attributes.slides || [];

			var blockProps = useBlockProps({
				className: 'parallax-hero-editor-container',
			});

			// --- Slide Update Helpers ---
			function updateSlide(index, updatedFields) {
				var newSlides = slides.map(function (slide, i) {
					if (i === index) {
						var updated = {};
						for (var key in slide) {
							if (slide.hasOwnProperty(key)) {
								updated[key] = slide[key];
							}
						}
						for (var k in updatedFields) {
							if (updatedFields.hasOwnProperty(k)) {
								updated[k] = updatedFields[k];
							}
						}
						return updated;
					}
					return slide;
				});
				setAttributes({ slides: newSlides });
			}

			function addSlide(initialData) {
				var newSlide = initialData || {
					image: null,
					depthMap: null,
					alt: '',
				};
				setAttributes({ slides: slides.concat([newSlide]) });
			}

			function removeSlide(index) {
				var newSlides = slides.filter(function (_, i) {
					return i !== index;
				});
				setAttributes({ slides: newSlides });
			}

			function moveSlide(index, direction) {
				var targetIndex = index + direction;
				if (targetIndex < 0 || targetIndex >= slides.length) return;
				var newSlides = slides.slice();
				var item = newSlides.splice(index, 1)[0];
				newSlides.splice(targetIndex, 0, item);
				setAttributes({ slides: newSlides });
			}

			function loadReferenceSlide() {
				addSlide({
					image: {
						id: 0,
						url: referenceImage,
						alt: 'Sample Mountain Landscape',
					},
					depthMap: {
						id: 0,
						url: referenceDepthMap,
					},
					alt: 'Sample Mountain Landscape',
				});
			}

			// --- Render Slide Card ---
			function renderSlideCard(slide, index) {
				var imageUrl = slide.image ? (slide.image.url || slide.image) : '';
				var depthUrl = slide.depthMap ? (slide.depthMap.url || slide.depthMap) : '';

				return el('div', {
					key: index,
					className: 'parallax-hero-slide-card',
				},
					// Header
					el('div', { className: 'parallax-hero-slide-card-header' },
						el('div', { className: 'parallax-hero-slide-title' },
							el('strong', null, __('Slide ', 'ltl-mtn') + (index + 1)),
							slide.alt ? el('span', { style: { color: '#aaa', marginLeft: '6px' } }, '— ' + slide.alt) : null
						),
						el('div', { className: 'parallax-hero-slide-actions' },
							el(Button, {
								onClick: function () { moveSlide(index, -1); },
								disabled: index === 0,
								size: 'small',
								variant: 'tertiary',
								title: __('Move Up', 'ltl-mtn'),
							}, '↑'),
							el(Button, {
								onClick: function () { moveSlide(index, 1); },
								disabled: index === slides.length - 1,
								size: 'small',
								variant: 'tertiary',
								title: __('Move Down', 'ltl-mtn'),
							}, '↓'),
							el(Button, {
								onClick: function () { removeSlide(index); },
								size: 'small',
								variant: 'tertiary',
								isDestructive: true,
								title: __('Remove Slide', 'ltl-mtn'),
							}, __('Remove', 'ltl-mtn'))
						)
					),
					// Slide Alt / Title Input
					el('div', { className: 'parallax-hero-slide-meta' },
						el(TextControl, {
							label: __('Slide Label / Alt Text', 'ltl-mtn'),
							value: slide.alt || '',
							placeholder: __('e.g. Mountain Landscape', 'ltl-mtn'),
							onChange: function (val) {
								updateSlide(index, { alt: val });
							},
						})
					),
					// Body (Two Columns: Hero Image & Depth Map)
					el('div', { className: 'parallax-hero-slide-card-body' },
						// Column 1: Hero Image
						el('div', { className: 'parallax-hero-media-field' },
							el('div', { className: 'parallax-hero-media-label' }, __('Hero Image (RGB)', 'ltl-mtn')),
							el('p', { className: 'parallax-hero-media-description' },
								__('Full-color 2:1 image displayed in the hero carousel.', 'ltl-mtn')
							),
							el('div', {
								className: 'parallax-hero-preview-box' + (imageUrl ? ' has-media' : ''),
							},
								imageUrl ? el('img', { src: imageUrl, alt: slide.alt || '' }) :
									el('div', { className: 'parallax-hero-preview-placeholder' },
										el('span', null, __('No image selected', 'ltl-mtn'))
									)
							),
							el('div', { className: 'parallax-hero-media-controls' },
								el(MediaUploadCheck, null,
									el(MediaUpload, {
										onSelect: function (media) {
											updateSlide(index, {
												image: {
													id: media.id,
													url: media.url,
													alt: media.alt || media.title || '',
												},
												alt: slide.alt || media.alt || media.title || '',
											});
										},
										allowedTypes: ['image'],
										value: slide.image && slide.image.id ? slide.image.id : undefined,
										render: function (obj) {
											return el(Button, {
												onClick: obj.open,
												variant: imageUrl ? 'secondary' : 'primary',
												size: 'compact',
											}, imageUrl ? __('Replace Image', 'ltl-mtn') : __('Select Image', 'ltl-mtn'));
										},
									})
								),
								imageUrl ? el(Button, {
									onClick: function () { updateSlide(index, { image: null }); },
									variant: 'tertiary',
									isDestructive: true,
									size: 'compact',
								}, __('Remove', 'ltl-mtn')) : null
							)
						),
						// Column 2: Depth Map
						el('div', { className: 'parallax-hero-media-field' },
							el('div', { className: 'parallax-hero-media-label' }, __('Depth Map', 'ltl-mtn')),
							el('p', { className: 'parallax-hero-media-description' },
								__('Grayscale or RGB depth map used to calculate 3D perspective shift.', 'ltl-mtn')
							),
							el('div', {
								className: 'parallax-hero-preview-box' + (depthUrl ? ' has-media' : ''),
							},
								depthUrl ? el('img', { src: depthUrl, alt: __('Depth map preview', 'ltl-mtn') }) :
									el('div', { className: 'parallax-hero-preview-placeholder' },
										el('span', null, __('No depth map selected', 'ltl-mtn'))
									)
							),
							el('div', { className: 'parallax-hero-media-controls' },
								el(MediaUploadCheck, null,
									el(MediaUpload, {
										onSelect: function (media) {
											updateSlide(index, {
												depthMap: {
													id: media.id,
													url: media.url,
												},
											});
										},
										allowedTypes: ['image'],
										value: slide.depthMap && slide.depthMap.id ? slide.depthMap.id : undefined,
										render: function (obj) {
											return el(Button, {
												onClick: obj.open,
												variant: depthUrl ? 'secondary' : 'primary',
												size: 'compact',
											}, depthUrl ? __('Replace Depth Map', 'ltl-mtn') : __('Select Depth Map', 'ltl-mtn'));
										},
									})
								),
								depthUrl ? el(Button, {
									onClick: function () { updateSlide(index, { depthMap: null }); },
									variant: 'tertiary',
									isDestructive: true,
									size: 'compact',
								}, __('Remove', 'ltl-mtn')) : null
							)
						)
					)
				);
			}

			return el(Fragment, null,
				// Sidebar Inspector Controls
				el(InspectorControls, null,
					el(PanelBody, { title: __('Carousel Settings', 'ltl-mtn'), initialOpen: true },
						el('p', null,
							el('strong', null, __('Total Slides: ', 'ltl-mtn')),
							slides.length
						),
						el(Button, {
							onClick: function () { addSlide(); },
							variant: 'secondary',
							style: { width: '100%', marginBottom: '10px' },
						}, __('+ Add New Slide', 'ltl-mtn')),
						el(Button, {
							onClick: loadReferenceSlide,
							variant: 'tertiary',
							style: { width: '100%' },
						}, __('Add Reference Sample Slide', 'ltl-mtn')),
						el('div', { className: 'parallax-hero-inspector-help' },
							__('Each slide displays at 2:1 full width on desktop with mouse-following 3D perspective, and at 1:1 right-anchored crop on mobile with scroll-driven displacement.', 'ltl-mtn')
						)
					)
				),
				// Main Block Canvas UI
				el('div', blockProps,
					// Header
					el('div', { className: 'parallax-hero-editor-header' },
						el('div', null,
							el('h3', null, '✦ ' + __('Parallax Hero Carousel', 'ltl-mtn')),
							el('p', null, __('Repeater Block: Configure hero slides with images and matching depth maps.', 'ltl-mtn'))
						),
						el('div', { className: 'parallax-hero-editor-header-actions' },
							el(Button, {
								onClick: function () { addSlide(); },
								variant: 'primary',
								size: 'compact',
							}, __('+ Add Slide', 'ltl-mtn'))
						)
					),
					// Slides List or Empty State
					slides.length === 0 ?
						el('div', { className: 'parallax-hero-empty-state' },
							el('h4', null, __('No Slides Added Yet', 'ltl-mtn')),
							el('p', null,
								__('Add your first slide with an image and depth map, or start with the reference sample images from the theme.', 'ltl-mtn')
							),
							el('div', { className: 'parallax-hero-empty-actions' },
								el(Button, {
									onClick: function () { addSlide(); },
									variant: 'secondary',
								}, __('+ Add Empty Slide', 'ltl-mtn')),
								el(Button, {
									onClick: loadReferenceSlide,
									className: 'parallax-hero-btn-accent',
									variant: 'primary',
								}, __('Load Reference Slide (Sample Hero + Depth)', 'ltl-mtn'))
							)
						) :
						el('div', { className: 'parallax-hero-slides-list' },
							slides.map(renderSlideCard),
							el('div', {
								className: 'parallax-hero-add-slide',
								onClick: function () { addSlide(); },
							},
								el(Button, {
									variant: 'tertiary',
								}, __('+ Add Another Slide', 'ltl-mtn'))
							)
						)
				)
			);
		},

		save: function () {
			// Dynamic block rendered via render.php
			return null;
		},
	});
})();
