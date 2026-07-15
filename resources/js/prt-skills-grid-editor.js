(function (wp) {
  if (!wp || !wp.blocks) return;
  var el = wp.element.createElement, Fragment = wp.element.Fragment, __ = wp.i18n.__;
  var be = wp.blockEditor || wp.editor, IC = be.InspectorControls, ubp = be.useBlockProps;
  var c = wp.components, SSR = wp.serverSideRender || wp.components.ServerSideRender;

  /**
   * Static, non-SSR skeleton shown while ServerSideRender's REST round-trip
   * is in flight (its LoadingResponsePlaceholder). The Patterns inserter and
   * "Choose a pattern" modal render pattern thumbnails in a lightweight
   * preview iframe that doesn't reliably wait for/complete the SSR fetch, so
   * without this the pattern preview can appear blank. This gives a real,
   * attribute-driven approximation instantly, no network round-trip needed.
   */
  function skeleton(a) {
    var items = [];
    try { items = JSON.parse(a.cards); } catch (e) { items = []; }
    var cols = a.columns || 3;
    return el('div', { style: { display: 'grid', gridTemplateColumns: 'repeat(' + cols + ', 1fr)', gap: 12, padding: '24px 0' } },
      items.map(function (item, i) {
        return el('div', { key: i, style: { border: '1px solid #e2e2e5', borderRadius: 10, padding: 16, background: a.style === 'focus' ? '#f2f0ff' : '#fff' } },
          el('strong', { style: { display: 'block', marginBottom: 6, fontSize: 15 } }, item.title || ''),
          el('span', { style: { display: 'block', fontSize: 13, color: '#646970' } }, item.body || '')
        );
      })
    );
  }

  function Repeater(items, onChange, defaultItem, renderRow) {
    return el('div', { className: 'prt-repeater' },
      items.map(function (item, i) {
        return el('div', { key: i, className: 'prt-repeater-row', style: { border: '1px solid #ddd', borderRadius: 6, padding: '10px 12px', marginBottom: 8, background: '#fafafa' } },
          renderRow(item, i, function (updates) {
            var next = items.slice(); next[i] = Object.assign({}, item, updates); onChange(next);
          }, function () {
            var next = items.slice(); next.splice(i, 1); onChange(next);
          })
        );
      }),
      el(c.Button, { variant: 'secondary', style: { marginTop: 6 }, onClick: function () { onChange(items.concat([defaultItem])); } }, '+ Add card')
    );
  }

  wp.blocks.registerBlockType('prt/skills-grid', {
    apiVersion: 2,
    title: __('Skills Grid', 'pressroot'),
    description: __('Grid of skill or feature cards — 2 or 3 columns.', 'pressroot'),
    icon: 'columns',
    category: 'pressroot',
    keywords: ['skills', 'features', 'cards', 'grid'],
    attributes: {
      cards:   { type: 'string', default: '[{"title":"Front-End","body":"HTML, CSS, JS, React, Tailwind"},{"title":"Back-End","body":"PHP, WordPress, Node.js, APIs"},{"title":"Accessibility","body":"WCAG 2.1, Core Web Vitals"}]' },
      columns: { type: 'number', default: 3 },
      style:   { type: 'string', default: 'default' }
    },
    edit: function (props) {
      var a = props.attributes;
      var items = [];
      try { items = JSON.parse(a.cards); } catch(e) { items = []; }
      var set = function (k) { return function (v) { var o = {}; o[k] = v; props.setAttributes(o); }; };

      var controls = el(IC, {},
        el(c.PanelBody, { title: __('Cards', 'pressroot'), initialOpen: true },
          Repeater(items, function(next){ set('cards')(JSON.stringify(next)); },
            { title: '', body: '' },
            function(item, i, update, remove) {
              return el(Fragment, {},
                el(c.TextControl, { label: __('Title', 'pressroot'), value: item.title || '', onChange: function(v){ update({title:v}); } }),
                el(c.TextareaControl, { label: __('Body', 'pressroot'), value: item.body || '', rows: 2, onChange: function(v){ update({body:v}); } }),
                el(c.Button, { isDestructive: true, variant: 'link', onClick: remove }, __('Remove', 'pressroot'))
              );
            }
          )
        ),
        el(c.PanelBody, { title: __('Layout', 'pressroot'), initialOpen: false },
          el(c.RangeControl, { label: __('Columns', 'pressroot'), value: a.columns, min: 2, max: 3, onChange: set('columns') }),
          el(c.SelectControl, { label: __('Card style', 'pressroot'), value: a.style,
            options: [{ label: __('Default (white card)', 'pressroot'), value: 'default' }, { label: __('Focus (green tint)', 'pressroot'), value: 'focus' }],
            onChange: set('style') })
        )
      );
      return el(Fragment, {}, controls, el('div', ubp ? ubp() : {}, el(SSR, {
        block: 'prt/skills-grid',
        attributes: a,
        LoadingResponsePlaceholder: function () { return skeleton(a); },
        EmptyResponsePlaceholder: function () { return skeleton(a); }
      })));
    },
    save: function () { return null; }
  });
})(window.wp);
