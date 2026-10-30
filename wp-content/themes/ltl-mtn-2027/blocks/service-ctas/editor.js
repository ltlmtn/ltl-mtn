/**
 * Service CTAs Block - Editor Implementation
 *
 * Implements a repeater block interface for managing Service CTA cards.
 * - Parent: ltl-mtn/service-ctas
 * - Child: ltl-mtn/service-cta
 *
 * Each CTA instance contains:
 * - Icon: A media upload field
 * - Eyebrow: A text field
 * - Heading: A text field
 * - Blurb: A textarea
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
	var TextareaControl = wp.components.TextareaControl;
	var __ = wp.i18n.__;

	var sampleData = (window.ltlMtnServiceCtasData && window.ltlMtnServiceCtasData.samples) || [
		{
			eyebrow: 'Driven by experience.',
			heading: 'Strategy &\nBranding',
			blurb: 'Effective brand strategies start with careful listening. We engage with our clients at a deep level, applying decades of experience and prioritizing measured goals over short-term hype.',
			iconUrl: (window.ltlMtnServiceCtasData && window.ltlMtnServiceCtasData.icons && window.ltlMtnServiceCtasData.icons.strategy) || '',
		},
		{
			eyebrow: 'Meticulous and bespoke.',
			heading: 'Design &\nDevelopment',
			blurb: 'We specialize in crafting unique, brand-centric websites that do exactly what you need them to and nothing that you don’t. No templates, no slop, no bloat. Just your authentic voice on the web.',
			iconUrl: (window.ltlMtnServiceCtasData && window.ltlMtnServiceCtasData.icons && window.ltlMtnServiceCtasData.icons.development) || '',
		},
		{
			eyebrow: 'Informed and effective.',
			heading: 'Marketing &\nManagement',
			blurb: 'We manage your digital presence with strategic rigor and heart. Combining the best available tools and hard-won intuition, we help you achieve progressive growth with credibility.',
			iconUrl: (window.ltlMtnServiceCtasData && window.ltlMtnServiceCtasData.icons && window.ltlMtnServiceCtasData.icons.marketing) || '',
		},
	];

	/* ==========================================================================
	   1. Parent Block: ltl-mtn/service-ctas
	   ========================================================================== */
	registerBlockType('ltl-mtn/service-ctas', {
		edit: function (props) {
			var blockProps = useBlockProps({
				className: 'service-ctas-editor-wrapper',
			});

			return el(Fragment, null,
				el(InspectorControls, null,
					el(PanelBody, { title: __('Service CTAs Information', 'ltl-mtn'), initialOpen: true },
						el('p', null,
							__('Each CTA card features an Icon, Eyebrow, and Heading in the gold header, with a Blurb textarea in the black card body beneath.', 'ltl-mtn')
						),
						el('p', { className: 'description' },
							__('The grid displays 1 column on mobile, 2 columns at 800px, and 3 columns at 1020px container width. The black blurb area expands uniformly across cards.', 'ltl-mtn')
						)
					)
				),
				el('div', blockProps,
					el('div', { className: 'service-ctas-editor-banner' },
						el('strong', null, '✦ ' + __('Service CTAs', 'ltl-mtn')),
						el('span', { style: { marginLeft: '8px', color: '#666' } },
							__('Add and edit service CTA repeater cards.', 'ltl-mtn')
						)
					),
					el('div', { className: 'service-ctas-editor-grid' },
						el(InnerBlocks, {
							allowedBlocks: ['ltl-mtn/service-cta'],
							template: [
								['ltl-mtn/service-cta', {
									eyebrow: sampleData[0].eyebrow,
									heading: sampleData[0].heading,
									blurb: sampleData[0].blurb,
									icon: sampleData[0].iconUrl ? { id: 0, url: sampleData[0].iconUrl, alt: 'Strategy Icon' } : null,
								}],
								['ltl-mtn/service-cta', {
									eyebrow: sampleData[1].eyebrow,
									heading: sampleData[1].heading,
									blurb: sampleData[1].blurb,
									icon: sampleData[1].iconUrl ? { id: 0, url: sampleData[1].iconUrl, alt: 'Development Icon' } : null,
								}],
								['ltl-mtn/service-cta', {
									eyebrow: sampleData[2].eyebrow,
									heading: sampleData[2].heading,
									blurb: sampleData[2].blurb,
									icon: sampleData[2].iconUrl ? { id: 0, url: sampleData[2].iconUrl, alt: 'Marketing Icon' } : null,
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
	   2. Child Block: ltl-mtn/service-cta
	   ========================================================================== */
	registerBlockType('ltl-mtn/service-cta', {
		apiVersion: 3,
		title: __('Service CTA', 'ltl-mtn'),
		category: 'theme',
		icon: 'format-aside',
		parent: ['ltl-mtn/service-ctas'],
		attributes: {
			icon: {
				type: 'object',
				default: null,
			},
			eyebrow: {
				type: 'string',
				default: '',
			},
			heading: {
				type: 'string',
				default: '',
			},
			blurb: {
				type: 'string',
				default: '',
			},
		},
		edit: function (props) {
			var attributes = props.attributes;
			var setAttributes = props.setAttributes;
			var blockProps = useBlockProps({
				className: 'service-cta-card-editor',
			});

			var iconUrl = attributes.icon ? (attributes.icon.url || attributes.icon) : '';
			var iconAlt = (attributes.icon && attributes.icon.alt) ? attributes.icon.alt : (attributes.heading || 'Icon');

			return el(Fragment, null,
				el(InspectorControls, null,
					el(PanelBody, { title: __('CTA Card Settings', 'ltl-mtn'), initialOpen: true },
						// Media Upload
						el('div', { style: { marginBottom: '16px' } },
							el('label', { style: { display: 'block', fontWeight: 600, marginBottom: '6px' } }, __('Icon', 'ltl-mtn')),
							el(MediaUploadCheck, null,
								el(MediaUpload, {
									onSelect: function (media) {
										setAttributes({
											icon: {
												id: media.id,
												url: media.url,
												alt: media.alt || media.title || '',
											},
										});
									},
									allowedTypes: ['image'],
									value: attributes.icon && attributes.icon.id ? attributes.icon.id : undefined,
									render: function (obj) {
										return el('div', { style: { display: 'flex', gap: '8px', alignItems: 'center' } },
											el(Button, {
												onClick: obj.open,
												variant: iconUrl ? 'secondary' : 'primary',
												size: 'compact',
											}, iconUrl ? __('Replace Icon', 'ltl-mtn') : __('Upload Icon', 'ltl-mtn')),
											iconUrl ? el(Button, {
												onClick: function () { setAttributes({ icon: null }); },
												variant: 'tertiary',
												isDestructive: true,
												size: 'compact',
											}, __('Remove', 'ltl-mtn')) : null
										);
									},
								})
							)
						),
						el(TextControl, {
							label: __('Eyebrow', 'ltl-mtn'),
							value: attributes.eyebrow || '',
							placeholder: __('e.g. Driven by experience.', 'ltl-mtn'),
							onChange: function (val) {
								setAttributes({ eyebrow: val });
							},
						}),
						el(TextControl, {
							label: __('Heading', 'ltl-mtn'),
							value: attributes.heading || '',
							placeholder: __('e.g. Strategy & Branding', 'ltl-mtn'),
							onChange: function (val) {
								setAttributes({ heading: val });
							},
						}),
						el(TextareaControl, {
							label: __('Blurb', 'ltl-mtn'),
							value: attributes.blurb || '',
							rows: 5,
							placeholder: __('Enter blurb text...', 'ltl-mtn'),
							onChange: function (val) {
								setAttributes({ blurb: val });
							},
						})
					)
				),
				// Canvas Card Display
				el('div', blockProps,
					// Gold Header Area
					el('div', { className: 'service-cta-card-header' },
						// Icon in top-left
						el('div', { className: 'service-cta-card-icon' },
							el(MediaUploadCheck, null,
								el(MediaUpload, {
									onSelect: function (media) {
										setAttributes({
											icon: {
												id: media.id,
												url: media.url,
												alt: media.alt || media.title || '',
											},
										});
									},
									allowedTypes: ['image'],
									value: attributes.icon && attributes.icon.id ? attributes.icon.id : undefined,
									render: function (obj) {
										return iconUrl ?
											el('button', {
												type: 'button',
												className: 'service-cta-icon-button',
												onClick: obj.open,
												title: __('Click to change icon', 'ltl-mtn'),
											},
												el('img', { src: iconUrl, alt: iconAlt })
											) :
											el(Button, {
												onClick: obj.open,
												variant: 'secondary',
												size: 'compact',
												className: 'service-cta-upload-btn',
											}, '+ ' + __('Icon', 'ltl-mtn'));
									},
								})
							)
						),
						// Eyebrow in top-right
						el('div', { className: 'service-cta-card-eyebrow' },
							el('input', {
								type: 'text',
								className: 'service-cta-inline-eyebrow',
								value: attributes.eyebrow || '',
								placeholder: __('Eyebrow...', 'ltl-mtn'),
								onChange: function (e) {
									setAttributes({ eyebrow: e.target.value });
								},
							})
						),
						// Heading in bottom-left
						el('div', { className: 'service-cta-card-heading' },
							el('textarea', {
								className: 'service-cta-inline-heading',
								rows: 2,
								value: attributes.heading || '',
								placeholder: __('Heading...', 'ltl-mtn'),
								onChange: function (e) {
									setAttributes({ heading: e.target.value });
								},
							})
						)
					),
					// Black Body Area with upward triangle notch
					el('div', { className: 'service-cta-card-body' },
						el('textarea', {
							className: 'service-cta-inline-blurb',
							rows: 4,
							value: attributes.blurb || '',
							placeholder: __('Enter blurb description...', 'ltl-mtn'),
							onChange: function (e) {
								setAttributes({ blurb: e.target.value });
							},
						})
					)
				)
			);
		},

		save: function () {
			return null;
		},
	});
})();
