/**
 * Pressroots Reserve — editor block registration.
 *
 * Vanilla @wordpress/* globals (no JSX/build step, matching the theme's other
 * hand-written block scripts). Previews via ServerSideRender so the editor
 * shows exactly what the front end renders, with an inspector control to pin
 * the form to one service (or let the visitor choose).
 */
(function (blocks, element, blockEditor, components, ssr, i18n) {
	'use strict';
	var el = element.createElement;
	var __ = i18n.__;
	var data = window.PRT_BOOKING_EDITOR || { services: [], brand: 'Pressroots Reserve' };

	var options = [{ label: __('Let the visitor choose', 'pressroot'), value: 0 }].concat(
		data.services.map(function (s) { return { label: s.title, value: s.id }; })
	);

	blocks.registerBlockType('prt/booking', {
		apiVersion: 3,
		title: __('Booking form', 'pressroot'),
		description: data.brand + ' — ' + __('appointments and table/room reservations.', 'pressroot'),
		icon: 'calendar-alt',
		category: 'widgets',
		keywords: ['booking', 'reservation', 'appointment', 'reserve'],
		supports: { align: ['wide'], html: false },
		attributes: {
			service: { type: 'number', default: 0 },
			accent: { type: 'string', default: '' }
		},
		edit: function (props) {
			var a = props.attributes;
			return el(
				element.Fragment, {},
				el(blockEditor.InspectorControls, {},
					el(components.PanelBody, { title: __('Booking form', 'pressroot'), initialOpen: true },
						el(components.SelectControl, {
							label: __('Service', 'pressroot'),
							value: a.service,
							options: options,
							onChange: function (v) { props.setAttributes({ service: parseInt(v, 10) || 0 }); }
						}),
						el(components.TextControl, {
							label: __('Accent color (optional)', 'pressroot'),
							help: __('Any CSS color. Blank uses your brand color.', 'pressroot'),
							value: a.accent,
							onChange: function (v) { props.setAttributes({ accent: v }); }
						})
					)
				),
				el('div', useBlockProps(),
					data.services.length
						? el(ssr, { block: 'prt/booking', attributes: a })
						: el(components.Placeholder, {
							icon: 'calendar-alt',
							label: data.brand,
							instructions: __('Add a service under Bookings → Services to preview the form here.', 'pressroot')
						})
				)
			);
			function useBlockProps() {
				return (blockEditor.useBlockProps ? blockEditor.useBlockProps() : {});
			}
		},
		save: function () { return null; } // server-rendered
	});
})(
	window.wp.blocks, window.wp.element, window.wp.blockEditor,
	window.wp.components, window.wp.serverSideRender, window.wp.i18n
);
