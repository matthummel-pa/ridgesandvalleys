(function (wp) {
  if (!wp || !wp.blocks) return;
  var el = wp.element.createElement, Fragment = wp.element.Fragment, __ = wp.i18n.__;
  var be = wp.blockEditor || wp.editor, IC = be.InspectorControls, ubp = be.useBlockProps;
  var c = wp.components, SSR = wp.serverSideRender || wp.components.ServerSideRender;

  /** Static, non-SSR skeleton for the Patterns-inserter preview — see the
   * matching comment in prt-skills-grid-editor.js for why this exists. */
  function skeleton(a) {
    var bg = a.variant === 'green' ? '#e8f5e9' : (a.variant === 'light' ? '#faf7f2' : '#17151F');
    var fg = a.variant === 'dark' ? '#fff' : '#17151F';
    return el('div', { style: { background: bg, color: fg, borderRadius: 12, padding: 28, textAlign: 'center' } },
      el('strong', { style: { display: 'block', fontSize: 22, marginBottom: 8 } }, a.heading || ''),
      el('p', { style: { margin: '0 0 14px', fontSize: 14, opacity: 0.85 } }, a.body || ''),
      el('span', { style: { display: 'inline-block', padding: '10px 22px', borderRadius: 999, background: fg === '#fff' ? '#fff' : '#17151F', color: fg === '#fff' ? '#17151F' : '#fff', fontSize: 13, fontWeight: 700 } }, a.btnText || '')
    );
  }

  wp.blocks.registerBlockType('prt/cta-band', {
    apiVersion: 2,
    title: __('CTA Band', 'pressroot'),
    description: __('A call-to-action section with heading, body, and button. Dark, green, or light variants.', 'pressroot'),
    icon: 'megaphone',
    category: 'pressroot',
    keywords: ['cta', 'call to action', 'band', 'section', 'button'],
    attributes: {
      heading: { type: 'string', default: 'Open to select side projects' },
      body:    { type: 'string', default: "I'm available for freelance work. Let's talk." },
      btnText: { type: 'string', default: 'Get in touch' },
      btnUrl:  { type: 'string', default: '/contact/' },
      variant: { type: 'string', default: 'dark' }
    },
    edit: function (props) {
      var a = props.attributes;
      var set = function (k) { return function (v) { var o = {}; o[k] = v; props.setAttributes(o); }; };

      var controls = el(IC, {},
        el(c.PanelBody, { title: __('Content', 'pressroot'), initialOpen: true },
          el(c.TextControl, { label: __('Heading', 'pressroot'), value: a.heading, onChange: set('heading') }),
          el(c.TextareaControl, { label: __('Body text', 'pressroot'), value: a.body, rows: 3, onChange: set('body') }),
          el(c.TextControl, { label: __('Button text', 'pressroot'), value: a.btnText, onChange: set('btnText') }),
          el(c.TextControl, { label: __('Button URL', 'pressroot'), value: a.btnUrl, type: 'url', onChange: set('btnUrl') })
        ),
        el(c.PanelBody, { title: __('Style', 'pressroot'), initialOpen: false },
          el(c.SelectControl, {
            label: __('Variant', 'pressroot'),
            value: a.variant,
            options: [
              { label: __('Dark (ink background)', 'pressroot'), value: 'dark' },
              { label: __('Green (brand colour)', 'pressroot'), value: 'green' },
              { label: __('Light (cream background)', 'pressroot'), value: 'light' }
            ],
            onChange: set('variant')
          })
        )
      );
      return el(Fragment, {}, controls, el('div', ubp ? ubp() : {}, el(SSR, {
        block: 'prt/cta-band',
        attributes: a,
        LoadingResponsePlaceholder: function () { return skeleton(a); },
        EmptyResponsePlaceholder: function () { return skeleton(a); }
      })));
    },
    save: function () { return null; }
  });
})(window.wp);
