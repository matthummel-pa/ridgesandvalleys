(function (wp) {
  if (!wp || !wp.blocks) return;
  var el = wp.element.createElement, Fragment = wp.element.Fragment, __ = wp.i18n.__;
  var be = wp.blockEditor || wp.editor, IC = be.InspectorControls, ubp = be.useBlockProps;
  var c = wp.components, SSR = wp.serverSideRender || wp.components.ServerSideRender;

  /** Static, non-SSR skeleton for the Patterns-inserter preview — see the
   * matching comment in prt-skills-grid-editor.js for why this exists. */
  function skeleton(a) {
    var items = [];
    try { items = JSON.parse(a.stats); } catch (e) { items = []; }
    var cols = a.columns || 4;
    return el('div', { style: { display: 'grid', gridTemplateColumns: 'repeat(' + cols + ', 1fr)', gap: 12, padding: '24px 0', textAlign: 'center' } },
      items.map(function (item, i) {
        return el('div', { key: i },
          el('strong', { style: { display: 'block', fontSize: 28 } }, item.value || ''),
          el('span', { style: { display: 'block', fontSize: 12, color: '#646970' } }, item.label || '')
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
      el(c.Button, { variant: 'secondary', style: { marginTop: 6 }, onClick: function () { onChange(items.concat([defaultItem])); } }, '+ Add stat')
    );
  }

  wp.blocks.registerBlockType('prt/stat-strip', {
    apiVersion: 2,
    title: __('Stat Strip', 'pressroot'),
    description: __('A row of highlighted stats/numbers (About page, hero sections).', 'pressroot'),
    icon: 'chart-bar',
    category: 'pressroot',
    keywords: ['stats', 'numbers', 'counter', 'about'],
    attributes: {
      stats:   { type: 'string', default: '[{"value":"15+","label":"Years experience"},{"value":"100+","label":"Projects"},{"value":"2","label":"Platforms"},{"value":"100%","label":"Remote"}]' },
      columns: { type: 'number', default: 4 }
    },
    edit: function (props) {
      var a = props.attributes;
      var items = [];
      try { items = JSON.parse(a.stats); } catch(e) { items = []; }
      var set = function (k) { return function (v) { var o = {}; o[k] = v; props.setAttributes(o); }; };

      var controls = el(IC, {},
        el(c.PanelBody, { title: __('Stats', 'pressroot'), initialOpen: true },
          Repeater(items, function(next){ set('stats')(JSON.stringify(next)); },
            { value: '', label: '' },
            function(item, i, update, remove) {
              return el(Fragment, {},
                el(c.TextControl, { label: __('Value', 'pressroot'), value: item.value || '', onChange: function(v){ update({value:v}); } }),
                el(c.TextControl, { label: __('Label', 'pressroot'), value: item.label || '', onChange: function(v){ update({label:v}); } }),
                el(c.Button, { isDestructive: true, variant: 'link', onClick: remove }, __('Remove', 'pressroot'))
              );
            }
          ),
          el(c.RangeControl, { label: __('Columns', 'pressroot'), value: a.columns, min: 2, max: 4, onChange: set('columns') })
        )
      );
      return el(Fragment, {}, controls, el('div', ubp ? ubp() : {}, el(SSR, {
        block: 'prt/stat-strip',
        attributes: a,
        LoadingResponsePlaceholder: function () { return skeleton(a); },
        EmptyResponsePlaceholder: function () { return skeleton(a); }
      })));
    },
    save: function () { return null; }
  });
})(window.wp);
