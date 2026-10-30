/**
 * Parallax Hero Block - Editor Implementation (No-Build)
 *
 * Implements a repeater block interface for managing carousel slides with inner blocks.
 * - Parent: ltl-mtn/parallax-hero
 * - Child: ltl-mtn/parallax-hero-slide
 *
 * Each slide instance contains:
 * - Hero Image (RGB)
 * - Depth Map
 * - Alt text / Heading
 * - Inner Blocks content area with background #EDEDED matching frontend var(--silver)
 *
 * Built with vanilla WordPress JavaScript (wp.element.createElement) without compilation.
 */

(function () {
	'use strict';

	var el = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var registerBlockType = wp.blocks.registerBlockType;
	var useBlockProps = wp.blockEditor.useBlockProps;
	var InnerBlocks = wp.blockEditor.InnerBlocks;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var MediaUpload = wp.blockEditor.MediaUpload;
	var MediaUploadCheck = wp.blockEditor.MediaUploadCheck;
	var PanelBody = wp.components.PanelBody;
	var Button = wp.components.Button;
	var TextControl = wp.components.TextControl;
	var __ = wp.i18n.__;

	// Reference media URLs (populated via PHP or default fallback)
	var referenceImage = (window.ltlMtnParallaxHeroData && window.ltlMtnParallaxHeroData.referenceImage) ||
		'/wp-content/themes/ltl-mtn-2027/blocks/parallax-hero/reference/sample-hero.jpg';
	var referenceDepthMap = (window.ltlMtnParallaxHeroData && window.ltlMtnParallaxHeroData.referenceDepthMap) ||
		'/wp-content/themes/ltl-mtn-2027/blocks/parallax-hero/reference/sample-hero.webp';

	/* ==========================================================================
	   1. Parent Carousel Block: ltl-mtn/parallax-hero
	   ========================================================================== */
	registerBlockType('ltl-mtn/parallax-hero', {
		edit: function (props) {
			var blockProps = useBlockProps({
				className: 'parallax-hero-carousel-editor',
			});

			return el(Fragment, null,
				el(InspectorControls, null,
					el(PanelBody, { title: __('Carousel Information', 'ltl-mtn'), initialOpen: true },
						el('p', null,
							__('For each slide, add a Hero Image, Depth Map, and inner block content.', 'ltl-mtn')
						),
						el('div', { className: 'parallax-hero-inspector-help' },
							__('Slides should have 2:1 aspect ratio, with the subject on the right side.', 'ltl-mtn')
						)
					)
				),
				el('div', blockProps,
					el('div', { className: 'parallax-hero-editor-header' },
						el('div', null,
							el('h3', null, '✦ ' + __('Parallax Hero Carousel', 'ltl-mtn')),
							el('p', null, __('Full-width carousel with 2.5D depth parallax and slide-advancing inner blocks.', 'ltl-mtn'))
						)
					),
					el('div', { className: 'parallax-hero-slides-wrapper' },
						el(InnerBlocks, {
							allowedBlocks: ['ltl-mtn/parallax-hero-slide'],
							template: [
								['ltl-mtn/parallax-hero-slide', {
									alt: 'Rocky Mountain Ridge',
									image: { id: 0, url: referenceImage, alt: 'Rocky Mountain Ridge' },
									depthMap: { id: 0, url: referenceDepthMap },
								}],
							],
							renderAppender: InnerBlocks.ButtonBlockAppender,
						})
					)
				)
			);
		},

		save: function () {
			return el(InnerBlocks.Content);
		},
	});

	/* ==========================================================================
	   2. Child Slide Block: ltl-mtn/parallax-hero-slide
	   ========================================================================== */
	registerBlockType('ltl-mtn/parallax-hero-slide', {
		apiVersion: 3,
		title: __('Parallax Hero Slide', 'ltl-mtn'),
		category: 'theme',
		icon: 'format-image',
		parent: ['ltl-mtn/parallax-hero'],
		attributes: {
			image: { type: 'object', default: null },
			depthMap: { type: 'object', default: null },
			alt: { type: 'string', default: '' },
		},
		edit: function (props) {
			var attributes = props.attributes;
			var setAttributes = props.setAttributes;
			var blockProps = useBlockProps({
				className: 'parallax-hero-slide-card',
			});

			var imageUrl = attributes.image ? (attributes.image.url || attributes.image) : '';
			var depthUrl = attributes.depthMap ? (attributes.depthMap.url || attributes.depthMap) : '';

			return el('div', blockProps,
				// Header
				el('div', { className: 'parallax-hero-slide-card-header' },
					el('div', { className: 'parallax-hero-slide-title' },
						el('strong', null, __('Parallax Hero Slide', 'ltl-mtn')),
						attributes.alt ? el('span', { style: { color: '#ccc', marginLeft: '8px' } }, '— ' + attributes.alt) : null
					)
				),
				// Heading / Alt text
				el('div', { className: 'parallax-hero-slide-meta' },
					el(TextControl, {
						label: __('Slide Heading / Alt Text (Heading for inner content)', 'ltl-mtn'),
						value: attributes.alt || '',
						placeholder: __('e.g. Mountain Panorama', 'ltl-mtn'),
						onChange: function (val) {
							setAttributes({ alt: val });
						},
					})
				),
				// Two Columns: Hero Image & Depth Map
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
							imageUrl ? el('img', { src: imageUrl, alt: attributes.alt || '' }) :
								el('div', { className: 'parallax-hero-preview-placeholder' },
									el('span', null, __('No image selected', 'ltl-mtn'))
								)
						),
						el('div', { className: 'parallax-hero-media-controls' },
							el(MediaUploadCheck, null,
								el(MediaUpload, {
									onSelect: function (media) {
										setAttributes({
											image: {
												id: media.id,
												url: media.url,
												alt: media.alt || media.title || '',
											},
											alt: attributes.alt || media.alt || media.title || '',
										});
									},
									allowedTypes: ['image'],
									value: attributes.image && attributes.image.id ? attributes.image.id : undefined,
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
								onClick: function () { setAttributes({ image: null }); },
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
										setAttributes({
											depthMap: {
												id: media.id,
												url: media.url,
											},
										});
									},
									allowedTypes: ['image'],
									value: attributes.depthMap && attributes.depthMap.id ? attributes.depthMap.id : undefined,
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
								onClick: function () { setAttributes({ depthMap: null }); },
								variant: 'tertiary',
								isDestructive: true,
								size: 'compact',
							}, __('Remove', 'ltl-mtn')) : null
						)
					)
				),
				// Use reference sample button if empty
				(!imageUrl && !depthUrl) ? el('div', { style: { padding: '0 16px 12px 16px' } },
					el(Button, {
						onClick: function () {
							setAttributes({
								image: { id: 0, url: referenceImage, alt: 'Sample Mountain Landscape' },
								depthMap: { id: 0, url: referenceDepthMap },
								alt: attributes.alt || 'Sample Mountain Landscape',
							});
						},
						variant: 'secondary',
						size: 'compact',
					}, __('Use Reference Sample Media', 'ltl-mtn'))
				) : null,
				// Inner Blocks Field (Background: #EDEDED to match frontend style)
				el('div', { className: 'parallax-hero-slide-inner-field' },
					el('div', { className: 'parallax-hero-inner-label' },
						__('Slide Inner Content (Placed in full-width var(--silver) container on frontend)', 'ltl-mtn')
					),
					el('div', { className: 'parallax-hero-slide-inner-box' },
						attributes.alt ? el('h3', { className: 'parallax-hero-inner-heading-preview' }, attributes.alt) : null,
						el(InnerBlocks, {
							template: [
								['core/paragraph', { placeholder: __('Enter slide content (paragraphs, buttons, etc.)...', 'ltl-mtn') }],
							],
							templateLock: false,
						})
					)
				)
			);
		},

		save: function () {
			return el(InnerBlocks.Content);
		},
	});
})();

