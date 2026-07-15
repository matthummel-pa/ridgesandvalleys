/**
 * Repofolio Repo Grid — editor script.
 *
 * Plain-JS (no JSX/build step) registration so the plugin stays drop-in
 * installable. Uses ServerSideRender for a live preview that matches the front
 * end, and an InspectorControls panel for source + per-feature toggles.
 */
( function ( wp ) {
	const { registerBlockType } = wp.blocks;
	const { createElement: el, Fragment } = wp.element;
	const { InspectorControls, useBlockProps } = wp.blockEditor || wp.editor;
	const {
		PanelBody,
		SelectControl,
		TextControl,
		RangeControl,
		ToggleControl,
		Notice,
	} = wp.components;
	const ServerSideRender = wp.serverSideRender;
	const { __ } = wp.i18n;

	const DATA = window.REPOFOLIO_BLOCK || { features: {}, defaults: {} };

	registerBlockType( 'repofolio/repo-grid', {
		apiVersion: 3,
		title: __( 'Repofolio Repo Grid', 'repofolio' ),
		description: __( 'A live grid of GitHub repositories with the details you choose.', 'repofolio' ),
		icon: 'grid-view',
		category: 'widgets',
		keywords: [ 'github', 'repo', 'repository', 'portfolio', 'repofolio' ],
		supports: { align: [ 'wide', 'full' ], html: false },

		attributes: ( function () {
			const a = {
				source: { type: 'string', default: '' },
				sourceLogin: { type: 'string', default: '' },
				sort: { type: 'string', default: '' },
				direction: { type: 'string', default: '' },
				perPage: { type: 'number', default: 0 },
				columns: { type: 'number', default: 0 },
				excludeForks: { type: 'string', default: 'inherit' },
				excludeArchived: { type: 'string', default: 'inherit' },
				overrideFeatures: { type: 'boolean', default: false },
			};
			Object.keys( DATA.features ).forEach( function ( key ) {
				a[ 'feature_' + key ] = { type: 'boolean', default: true };
			} );
			return a;
		} )(),

		edit: function ( props ) {
			const { attributes: att, setAttributes } = props;
			const blockProps = useBlockProps();

			const featureToggles = Object.keys( DATA.features ).map( function ( key ) {
				return el( ToggleControl, {
					key: key,
					label: DATA.features[ key ],
					checked: !! att[ 'feature_' + key ],
					onChange: function ( v ) {
						const u = {};
						u[ 'feature_' + key ] = v;
						setAttributes( u );
					},
				} );
			} );

			const inspector = el(
				InspectorControls,
				{},
				el(
					PanelBody,
					{ title: __( 'Data source', 'repofolio' ), initialOpen: true },
					el( SelectControl, {
						label: __( 'Repositories', 'repofolio' ),
						value: att.source,
						options: [
							{ label: __( 'Use plugin default', 'repofolio' ), value: '' },
							{ label: __( 'My connected account', 'repofolio' ), value: 'authenticated' },
							{ label: __( 'A specific user', 'repofolio' ), value: 'user' },
							{ label: __( 'An organization', 'repofolio' ), value: 'org' },
						],
						onChange: function ( v ) { setAttributes( { source: v } ); },
					} ),
					( att.source === 'user' || att.source === 'org' ) &&
						el( TextControl, {
							label: __( 'User / org login', 'repofolio' ),
							value: att.sourceLogin,
							onChange: function ( v ) { setAttributes( { sourceLogin: v } ); },
						} ),
					el( SelectControl, {
						label: __( 'Sort by', 'repofolio' ),
						value: att.sort,
						options: [
							{ label: __( 'Use plugin default', 'repofolio' ), value: '' },
							{ label: __( 'Recently updated', 'repofolio' ), value: 'updated' },
							{ label: __( 'Recently pushed', 'repofolio' ), value: 'pushed' },
							{ label: __( 'Newest', 'repofolio' ), value: 'created' },
							{ label: __( 'Name', 'repofolio' ), value: 'full_name' },
							{ label: __( 'Most stars', 'repofolio' ), value: 'stars' },
						],
						onChange: function ( v ) { setAttributes( { sort: v } ); },
					} ),
					el( RangeControl, {
						label: __( 'How many (0 = default)', 'repofolio' ),
						value: att.perPage,
						min: 0,
						max: 50,
						onChange: function ( v ) { setAttributes( { perPage: v } ); },
					} ),
					el( RangeControl, {
						label: __( 'Columns (0 = default)', 'repofolio' ),
						value: att.columns,
						min: 0,
						max: 4,
						onChange: function ( v ) { setAttributes( { columns: v } ); },
					} ),
					el( SelectControl, {
						label: __( 'Hide forks', 'repofolio' ),
						value: att.excludeForks,
						options: [
							{ label: __( 'Use plugin default', 'repofolio' ), value: 'inherit' },
							{ label: __( 'Yes', 'repofolio' ), value: 'yes' },
							{ label: __( 'No', 'repofolio' ), value: 'no' },
						],
						onChange: function ( v ) { setAttributes( { excludeForks: v } ); },
					} ),
					el( SelectControl, {
						label: __( 'Hide archived', 'repofolio' ),
						value: att.excludeArchived,
						options: [
							{ label: __( 'Use plugin default', 'repofolio' ), value: 'inherit' },
							{ label: __( 'Yes', 'repofolio' ), value: 'yes' },
							{ label: __( 'No', 'repofolio' ), value: 'no' },
						],
						onChange: function ( v ) { setAttributes( { excludeArchived: v } ); },
					} )
				),
				el(
					PanelBody,
					{ title: __( 'What to display', 'repofolio' ), initialOpen: false },
					el( ToggleControl, {
						label: __( 'Override plugin defaults', 'repofolio' ),
						checked: !! att.overrideFeatures,
						onChange: function ( v ) { setAttributes( { overrideFeatures: v } ); },
					} ),
					att.overrideFeatures
						? featureToggles
						: el( Notice, { status: 'info', isDismissible: false }, __( 'Using the display settings from Settings → Repofolio.', 'repofolio' ) )
				)
			);

			const preview = el( ServerSideRender, {
				block: 'repofolio/repo-grid',
				attributes: att,
			} );

			return el( Fragment, {}, inspector, el( 'div', blockProps, preview ) );
		},

		// Server-rendered: nothing saved to post content.
		save: function () {
			return null;
		},
	} );
} )( window.wp );
