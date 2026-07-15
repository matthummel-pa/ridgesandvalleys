(function (wp) {
  if (!wp || !wp.blocks) return;
  var el = wp.element.createElement, Fragment = wp.element.Fragment, __ = wp.i18n.__;
  var be = wp.blockEditor || wp.editor, IC = be.InspectorControls, ubp = be.useBlockProps;
  var c = wp.components, SSR = wp.serverSideRender || wp.components.ServerSideRender;

  /** Static, non-SSR skeleton for the Patterns-inserter preview — see the
   * matching comment in prt-skills-grid-editor.js for why this exists. */
  function skeleton(a) {
    var items = [];
    try { items = JSON.parse(a.entries); } catch (e) { items = []; }
    return el('div', { style: { padding: '24px 0' } },
      items.map(function (item, i) {
        return el('div', { key: i, style: { borderLeft: '2px solid #ddd', paddingLeft: 16, marginBottom: 16 } },
          el('span', { style: { display: 'block', fontSize: 12, color: '#646970' } }, item.dates || ''),
          el('strong', { style: { display: 'block', fontSize: 16, margin: '2px 0' } }, item.title || ''),
          el('span', { style: { display: 'block', fontSize: 13, color: '#646970' } }, item.org || ''),
          el('p', { style: { fontSize: 13, margin: '4px 0 0' } }, item.body || '')
        );
      })
    );
  }

  function Repeater(items, onChange, defaultItem, renderRow) {
    return el('div', { className: 'prt-repeater' },
      items.map(function (item, i) {
        return el('div', { key: i, style: { border: '1px solid #ddd', borderRadius: 6, padding: '10px 12px', marginBottom: 8, background: '#fafafa' } },
          renderRow(item, i, function (updates) {
            var next = items.slice(); next[i] = Object.assign({}, item, updates); onChange(next);
          }, function () {
            var next = items.slice(); next.splice(i, 1); onChange(next);
          })
        );
      }),
      el(c.Button, { variant: 'secondary', style: { marginTop: 6 }, onClick: function () { onChange(items.concat([defaultItem])); } }, '+ Add entry')
    );
  }

  wp.blocks.registerBlockType('prt/timeline', {
    apiVersion: 2,
    title: __('Timeline', 'pressroot'),
    description: __('Work history / career timeline. Used on the Résumé page.', 'pressroot'),
    icon: 'list-view',
    category: 'pressroot',
    keywords: ['timeline', 'resume', 'experience', 'history', 'work'],
    attributes: {
      entries: { type: 'string', default: '[{"dates":"2021–Present","title":"Senior Power Platform Consultant","org":"Various clients · Remote","body":"Power Apps, Power Automate, SharePoint, M365 integrations."}]' }
    },
    edit: function (props) {
      var a = props.attributes;
      var items = [];
      try { items = JSON.parse(a.entries); } catch(e) { items = []; }
      var setEntries = function(next) { props.setAttributes({ entries: JSON.stringify(next) }); };

      var controls = el(IC, {},
        el(c.PanelBody, { title: __('Timeline entries', 'pressroot'), initialOpen: true },
          Repeater(items, setEntries,
            { dates: '', title: '', org: '', body: '' },
            function(item, i, update, remove) {
              return el(Fragment, {},
                el(c.TextControl, { label: __('Dates', 'pressroot'), value: item.dates || '', placeholder: '2020–Present', onChange: function(v){ update({dates:v}); } }),
                el(c.TextControl, { label: __('Job title', 'pressroot'), value: item.title || '', onChange: function(v){ update({title:v}); } }),
                el(c.TextControl, { label: __('Organisation', 'pressroot'), value: item.org || '', placeholder: 'Company · Location', onChange: function(v){ update({org:v}); } }),
                el(c.TextareaControl, { label: __('Description', 'pressroot'), value: item.body || '', rows: 3, onChange: function(v){ update({body:v}); } }),
                el(c.Button, { isDestructive: true, variant: 'link', onClick: remove }, __('Remove', 'pressroot'))
              );
            }
          )
        )
      );
      return el(Fragment, {}, controls, el('div', ubp ? ubp() : {}, el(SSR, {
        block: 'prt/timeline',
        attributes: a,
        LoadingResponsePlaceholder: function () { return skeleton(a); },
        EmptyResponsePlaceholder: function () { return skeleton(a); }
      })));
    },
    save: function () { return null; }
  });
})(window.wp);
