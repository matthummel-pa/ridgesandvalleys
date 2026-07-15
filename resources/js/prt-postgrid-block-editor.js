(function (wp) {
  if (!wp || !wp.blocks) return;
  var el = wp.element.createElement, Fragment = wp.element.Fragment, __ = wp.i18n.__;
  var be = wp.blockEditor || wp.editor, IC = be.InspectorControls, ubp = be.useBlockProps;
  var c = wp.components, SSR = wp.serverSideRender || wp.components.ServerSideRender;

  /** Static, non-SSR skeleton for the Patterns-inserter preview — see the
   * matching comment in prt-skills-grid-editor.js for why this exists. Can't
   * show real post data (that's the whole point of the block), so this shows
   * generic placeholder cards matching the chosen column count / toggles. */
  function skeleton(a) {
    var cols = a.columns || 3;
    var n = Math.min(a.count || 3, cols * 2);
    return el('div', { style: { display: 'grid', gridTemplateColumns: 'repeat(' + cols + ', 1fr)', gap: 12, padding: '24px 0' } },
      Array.apply(null, { length: n }).map(function (_, i) {
        return el('div', { key: i, style: { border: '1px solid #e2e2e5', borderRadius: 10, overflow: 'hidden', background: '#fff' } },
          a.showImage ? el('div', { style: { height: 100, background: '#f0f0f2' } }) : null,
          el('div', { style: { padding: 14 } },
            a.showCategory ? el('span', { style: { fontSize: 11, color: '#6C4CF1', fontWeight: 700, display: 'block', marginBottom: 4 } }, 'Category') : null,
            el('strong', { style: { display: 'block', fontSize: 14, marginBottom: 4 } }, 'Post title'),
            a.showExcerpt ? el('span', { style: { fontSize: 12, color: '#646970' } }, 'Excerpt preview text goes here…') : null,
            a.showDate ? el('span', { style: { display: 'block', fontSize: 11, color: '#8c8f94', marginTop: 6 } }, 'Jan 1, 2026') : null
          )
        );
      })
    );
  }

  wp.blocks.registerBlockType('prt/post-grid', {
    apiVersion: 2,
    title: __('Post Grid', 'pressroot'),
    description: __('Query posts or projects into a responsive card grid.', 'pressroot'),
    icon: 'grid-view',
    category: 'widgets',
    keywords: ['posts', 'projects', 'grid', 'cards'],
    supports: { align: ['wide', 'full'] },
    attributes: {
      postType:    { type: 'string',  default: 'post' },
      count:       { type: 'number',  default: 6 },
      columns:     { type: 'number',  default: 3 },
      orderby:     { type: 'string',  default: 'date' },
      order:       { type: 'string',  default: 'DESC' },
      showImage:   { type: 'boolean', default: true },
      showExcerpt: { type: 'boolean', default: true },
      showDate:    { type: 'boolean', default: true },
      showCategory:{ type: 'boolean', default: false }
    },
    edit: function (props) {
      var a = props.attributes, set = function (k){ return function (v){ var o={}; o[k]=v; props.setAttributes(o); }; };
      var controls = el(IC, {},
        el(c.PanelBody, { title: __('Query', 'pressroot'), initialOpen: true },
          el(c.SelectControl, { label: __('Post type', 'pressroot'), value: a.postType,
            options: [{label:'Posts',value:'post'},{label:'Projects',value:'projects'},{label:'Pages',value:'page'}], onChange: set('postType') }),
          el(c.RangeControl, { label: __('Number of items', 'pressroot'), value: a.count, min: 1, max: 24, onChange: set('count') }),
          el(c.RangeControl, { label: __('Columns', 'pressroot'), value: a.columns, min: 1, max: 4, onChange: set('columns') }),
          el(c.SelectControl, { label: __('Order by', 'pressroot'), value: a.orderby,
            options: [{label:'Date',value:'date'},{label:'Title',value:'title'},{label:'Random',value:'rand'},{label:'Menu order',value:'menu_order'}], onChange: set('orderby') }),
          el(c.SelectControl, { label: __('Order', 'pressroot'), value: a.order,
            options: [{label:'Descending',value:'DESC'},{label:'Ascending',value:'ASC'}], onChange: set('order') })
        ),
        el(c.PanelBody, { title: __('Card content', 'pressroot'), initialOpen: false },
          el(c.ToggleControl, { label: __('Featured image', 'pressroot'), checked: !!a.showImage, onChange: set('showImage') }),
          el(c.ToggleControl, { label: __('Excerpt', 'pressroot'), checked: !!a.showExcerpt, onChange: set('showExcerpt') }),
          el(c.ToggleControl, { label: __('Date', 'pressroot'), checked: !!a.showDate, onChange: set('showDate') }),
          el(c.ToggleControl, { label: __('Category', 'pressroot'), checked: !!a.showCategory, onChange: set('showCategory') })
        )
      );
      return el(Fragment, {}, controls, el('div', ubp ? ubp() : {}, el(SSR, {
        block: 'prt/post-grid',
        attributes: a,
        LoadingResponsePlaceholder: function () { return skeleton(a); },
        EmptyResponsePlaceholder: function () { return skeleton(a); }
      })));
    },
    save: function () { return null; }
  });
})(window.wp);
