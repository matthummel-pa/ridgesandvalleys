/* Social Icons block editor (no build step — uses global wp.* packages). */
(function (wp) {
  if (!wp || !wp.blocks) return;

  var el = wp.element.createElement;
  var Fragment = wp.element.Fragment;
  var __ = wp.i18n.__;
  var be = wp.blockEditor || wp.editor;
  var InspectorControls = be.InspectorControls;
  var useBlockProps = be.useBlockProps;
  var c = wp.components;
  var ServerSideRender = wp.serverSideRender || wp.components.ServerSideRender;

  var cfg = window.mhSocialBlock || { attrs: {}, defaults: {}, networks: {} };

  // Build attributes from PHP-provided schema.
  var attributes = {};
  Object.keys(cfg.attrs).forEach(function (k) {
    attributes[k] = { type: cfg.attrs[k].type, default: cfg.attrs[k].default };
  });

  function PanelColor(label, value, onChange) {
    return el(c.BaseControl, { label: label },
      el(c.ColorPalette, { value: value, onChange: onChange, clearable: true })
    );
  }

  wp.blocks.registerBlockType('prt/social-links', {
    apiVersion: 2,
    title: __('Social Icons', 'pressroot'),
    description: __('Inline SVG social icons (Blade Icons). Pulls from your site social links by default.', 'pressroot'),
    icon: 'share',
    category: 'widgets',
    keywords: ['social', 'icons', 'links', 'share'],
    supports: { align: ['wide', 'full'], html: false },
    attributes: attributes,

    edit: function (props) {
      var a = props.attributes;
      var set = function (k) { return function (v) { var o = {}; o[k] = v; props.setAttributes(o); }; };
      var blockProps = useBlockProps ? useBlockProps() : {};

      var controls = el(InspectorControls, {},
        el(c.PanelBody, { title: __('Content', 'pressroot'), initialOpen: true },
          el(c.SelectControl, {
            label: __('Source', 'pressroot'),
            value: a.source,
            options: [
              { label: __('Site social links', 'pressroot'), value: 'site' },
              { label: __('Custom links', 'pressroot'), value: 'custom' }
            ],
            onChange: set('source')
          }),
          a.source === 'custom' ? el(c.TextareaControl, {
            label: __('Custom links', 'pressroot'),
            help: __('One per line, as: network|https://url  (e.g. github|https://github.com/you)', 'pressroot'),
            value: a.customLinks,
            onChange: set('customLinks')
          }) : el('p', { style: { fontSize: '12px', opacity: 0.7 } },
            __('Showing the URLs from Customizer → Theme Options → Menu & Popout.', 'pressroot'))
        ),

        el(c.PanelBody, { title: __('Layout', 'pressroot'), initialOpen: false },
          el(c.SelectControl, {
            label: __('Alignment', 'pressroot'), value: a.align,
            options: [
              { label: __('Left', 'pressroot'), value: 'left' },
              { label: __('Center', 'pressroot'), value: 'center' },
              { label: __('Right', 'pressroot'), value: 'right' }
            ], onChange: set('align')
          }),
          el(c.ToggleControl, { label: __('Open links in new tab', 'pressroot'), checked: !!a.newTab, onChange: set('newTab') })
        ),

        el(c.PanelBody, { title: __('Style & color', 'pressroot'), initialOpen: false },
          el(c.ToggleControl, {
            label: __('Match site social icon style', 'pressroot'),
            help: __('Use the size/shape/color set in Customizer → Theme Options → Social Icons, so this always matches the header, footer, and popout icons.', 'pressroot'),
            checked: !!a.matchSite,
            onChange: set('matchSite')
          }),
          !a.matchSite && el(c.RangeControl, { label: __('Icon size (px)', 'pressroot'), value: a.size, min: 12, max: 64, onChange: set('size') }),
          !a.matchSite && el(c.RangeControl, { label: __('Gap (px)', 'pressroot'), value: a.gap, min: 0, max: 48, onChange: set('gap') }),
          !a.matchSite && el(c.SelectControl, {
            label: __('Shape', 'pressroot'), value: a.shape,
            options: [
              { label: __('None (plain)', 'pressroot'), value: 'none' },
              { label: __('Circle', 'pressroot'), value: 'circle' },
              { label: __('Rounded', 'pressroot'), value: 'rounded' },
              { label: __('Square', 'pressroot'), value: 'square' }
            ], onChange: set('shape')
          }),
          !a.matchSite && el(c.SelectControl, {
            label: __('Icon style', 'pressroot'), value: a.iconStyle,
            options: [
              { label: __('Mono (theme color)', 'pressroot'), value: 'mono' },
              { label: __('Brand colors', 'pressroot'), value: 'brand' }
            ], onChange: set('iconStyle'),
            help: a.iconStyle === 'brand' ? __('Each icon uses its official brand color.', 'pressroot') : ''
          }),
          (!a.matchSite && a.iconStyle === 'mono') ? PanelColor(__('Icon color', 'pressroot'), a.color, set('color')) : null,
          (!a.matchSite && a.shape !== 'none') ? PanelColor(__('Chip background', 'pressroot'), a.bg, set('bg')) : null,
          (!a.matchSite && a.iconStyle === 'mono') ? PanelColor(__('Hover icon color', 'pressroot'), a.hoverColor, set('hoverColor')) : null,
          (!a.matchSite && a.iconStyle === 'mono' && a.shape !== 'none') ? PanelColor(__('Hover chip background', 'pressroot'), a.hoverBg, set('hoverBg')) : null
        )
      );

      var preview = el(ServerSideRender, {
        block: 'prt/social-links',
        attributes: a
      });

      return el(Fragment, {}, controls, el('div', blockProps, preview));
    },

    save: function () { return null; } // dynamic — rendered in PHP
  });
})(window.wp);
