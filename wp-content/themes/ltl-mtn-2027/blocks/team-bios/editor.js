/**
 * Team Bios Block - Editor Implementation
 *
 * Implements a repeater block interface for managing Team Bio cards.
 * - Parent: ltl-mtn/team-bios
 * - Child: ltl-mtn/team-bio
 *
 * Each Team Bio instance contains:
 * - Headshot Image: A media upload field
 * - Name: A text field
 * - Bio: A rich text field
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
	var RichText = wp.blockEditor.RichText;
	var PanelBody = wp.components.PanelBody;
	var Button = wp.components.Button;
	var TextControl = wp.components.TextControl;
	var __ = wp.i18n.__;

	/* ==========================================================================
	   1. Parent Block: ltl-mtn/team-bios
	   ========================================================================== */
	registerBlockType('ltl-mtn/team-bios', {
		edit: function (props) {
			var blockProps = useBlockProps({
				className: 'team-bios-editor-wrapper',
			});

			return el(Fragment, null,
				el(InspectorControls, null,
					el(PanelBody, { title: __('Team Bios Information', 'ltl-mtn'), initialOpen: true },
						el('p', null,
							__('Add and rearrange team bio cards. Each entry includes a headshot, name, and optional rich text bio.', 'ltl-mtn')
						),
						el('p', { className: 'description' },
							__('On the frontend, headshots are displayed in grayscale (fading to color on hover) in a grid up to 175px wide. If an entry has a bio, a gold "+" icon appears in the corner to toggle the bio overlay.', 'ltl-mtn')
						)
					)
				),
				el('div', blockProps,
					el('div', { className: 'team-bios-editor-banner' },
						el('strong', null, '✦ ' + __('Team Bios', 'ltl-mtn')),
						el('span', { style: { marginLeft: '8px', color: '#666' } },
							__('Repeater grid of team member bios.', 'ltl-mtn')
						)
					),
					el('div', { className: 'team-bios-editor-grid' },
						el(InnerBlocks, {
							allowedBlocks: ['ltl-mtn/team-bio'],
							renderAppender: InnerBlocks.ButtonBlockAppender,
							orientation: 'horizontal',
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
	   2. Child Block: ltl-mtn/team-bio
	   ========================================================================== */
	registerBlockType('ltl-mtn/team-bio', {
		apiVersion: 3,
		title: __('Team Bio', 'ltl-mtn'),
		category: 'theme',
		icon: 'admin-users',
		parent: ['ltl-mtn/team-bios'],
		attributes: {
			image: {
				type: 'object',
				default: null,
			},
			name: {
				type: 'string',
				default: '',
			},
			bio: {
				type: 'string',
				default: '',
			},
		},
		supports: {
			html: false,
			reusable: false,
			inserter: true,
		},
		edit: function (props) {
			var attributes = props.attributes;
			var setAttributes = props.setAttributes;
			var blockProps = useBlockProps({
				className: 'team-bio-card-editor',
			});

			var imageUrl = attributes.image ? (attributes.image.url || attributes.image) : '';
			var imageAlt = (attributes.image && attributes.image.alt) ? attributes.image.alt : (attributes.name || 'Headshot');

			return el(Fragment, null,
				el(InspectorControls, null,
					el(PanelBody, { title: __('Team Member Details', 'ltl-mtn'), initialOpen: true },
						// Media Upload Field
						el('div', { style: { marginBottom: '16px' } },
							el('label', { style: { display: 'block', fontWeight: 600, marginBottom: '6px' } }, __('Headshot Image', 'ltl-mtn')),
							el(MediaUploadCheck, null,
								el(MediaUpload, {
									onSelect: function (media) {
										setAttributes({
											image: {
												id: media.id,
												url: media.url,
												alt: media.alt || media.title || attributes.name || '',
											},
										});
									},
									allowedTypes: ['image'],
									value: attributes.image && attributes.image.id ? attributes.image.id : undefined,
									render: function (obj) {
										return el('div', { style: { display: 'flex', gap: '8px', alignItems: 'center' } },
											el(Button, {
												onClick: obj.open,
												variant: imageUrl ? 'secondary' : 'primary',
												size: 'compact',
											}, imageUrl ? __('Replace Image', 'ltl-mtn') : __('Upload Headshot', 'ltl-mtn')),
											imageUrl ? el(Button, {
												onClick: function () { setAttributes({ image: null }); },
												variant: 'tertiary',
												isDestructive: true,
												size: 'compact',
											}, __('Remove', 'ltl-mtn')) : null
										);
									},
								})
							)
						),
						// Name Field
						el(TextControl, {
							label: __('Team Member Name', 'ltl-mtn'),
							value: attributes.name || '',
							placeholder: __('e.g. Steve Koester', 'ltl-mtn'),
							onChange: function (val) {
								setAttributes({ name: val });
							},
						})
					)
				),
				// Canvas Card Display
				el('div', blockProps,
					// Headshot Media Area
					el('div', { className: 'team-bio-editor-headshot-wrap' },
						el(MediaUploadCheck, null,
							el(MediaUpload, {
								onSelect: function (media) {
									setAttributes({
										image: {
											id: media.id,
											url: media.url,
											alt: media.alt || media.title || attributes.name || '',
										},
									});
								},
								allowedTypes: ['image'],
								value: attributes.image && attributes.image.id ? attributes.image.id : undefined,
								render: function (obj) {
									return imageUrl ?
										el('div', { className: 'team-bio-editor-image-preview', onClick: obj.open, title: __('Click to change headshot', 'ltl-mtn') },
											el('img', { src: imageUrl, alt: imageAlt }),
											el('div', { className: 'team-bio-editor-image-overlay-hint' }, __('Change', 'ltl-mtn'))
										) :
										el('div', { className: 'team-bio-editor-image-placeholder' },
											el(Button, {
												onClick: obj.open,
												variant: 'secondary',
												size: 'compact',
												className: 'team-bio-upload-btn',
											}, '+ ' + __('Headshot', 'ltl-mtn'))
										);
								},
							})
						),
						// Plus icon indicator in bottom right (if bio is present)
						attributes.bio ? el('div', { className: 'team-bio-editor-plus-indicator', title: __('Bio overlay active with "+"', 'ltl-mtn') }, '+') : null
					),
					// Card Content Area
					el('div', { className: 'team-bio-editor-details' },
						// Name Text Field
						el('div', { className: 'team-bio-editor-name-field' },
							el(TextControl, {
								label: __('Name', 'ltl-mtn'),
								value: attributes.name || '',
								placeholder: __('Team Member Name', 'ltl-mtn'),
								onChange: function (val) {
									setAttributes({ name: val });
								},
							})
						),
						// Bio Rich Text Field
						el('div', { className: 'team-bio-editor-bio-field' },
							el('label', { className: 'team-bio-editor-bio-label' }, __('Bio (Rich Text)', 'ltl-mtn')),
							el(RichText, {
								tagName: 'div',
								className: 'team-bio-editor-richtext',
								value: attributes.bio || '',
								placeholder: __('Enter bio content (optional)...', 'ltl-mtn'),
								onChange: function (val) {
									setAttributes({ bio: val });
								},
							})
						)
					)
				)
			);
		},

		save: function () {
			return null;
		},
	});
})();
