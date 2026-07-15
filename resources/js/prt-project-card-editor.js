(function (wp) {
  if (!wp || !wp.blocks) return;
  var el = wp.element.createElement, Fragment = wp.element.Fragment, __ = wp.i18n.__;
  var be = wp.blockEditor || wp.editor, IC = be.InspectorControls, ubp = be.useBlockProps;
  var c = wp.components, SSR = wp.serverSideRender || wp.components.ServerSideRender;

  /** Static, non-SSR skeleton for the Patterns-inserter preview — see the
   * matching comment in prt-skills-grid-editor.js for why this exists. */
  function skeleton(a) {
    var tags = (a.tags || '').split(',').map(function (t) { return t.trim(); }).filter(Boolean);
    return el('div', { style: { border: '1px solid #e2e2e5', borderRadius: 10, overflow: 'hidden', background: '#fff' } },
      el('div', { style: { height: 120, background: a.imageUrl ? ('url(' + a.imageUrl + ') center/cover') : '#f0f0f2' } }),
      el('div', { style: { padding: 16 } },
        el('strong', { style: { display: 'block', fontSize: 16, marginBottom: 6 } }, a.heading || ''),
        el('p', { style: { fontSize: 13, color: '#646970', margin: '0 0 10px' } }, a.excerpt || ''),
        el('div', { style: { display: 'flex', gap: 6, flexWrap: 'wrap' } },
          tags.map(function (t, i) {
            return el('span', { key: i, style: { fontSize: 11, padding: '3px 9px', borderRadius: 999, background: '#f0f0f2', color: '#3c434a' } }, t);
          })
        )
      )
    );
  }

  wp.blocks.registerBlockType('prt/project-card', {
    apiVersion: 2,
    title: __('Project Card', 'pressroot'),
    description: __('A bespoke project card with image, tags, and links. Use inside columns for a manual project grid.', 'pressroot'),
    icon: 'portfolio',
    category: 'pressroot',
    keywords: ['project', 'card', 'portfolio', 'work'],
    attributes: {
      heading:   { type: 'string', default: 'Project Title' },
      excerpt:   { type: 'string', default: 'Short description of this project.' },
      link:      { type: 'string', default: '' },
      imageUrl:  { type: 'string', default: '' },
      imageAlt:  { type: 'string', default: '' },
      tags:      { type: 'string', default: 'React, Tailwind, Supabase' },
      liveUrl:   { type: 'string', default: '' },
      githubUrl: { type: 'string', default: '' }
    },
    edit: function (props) {
      var a = props.attributes;
      var set = function (k) { return function (v) { var o = {}; o[k] = v; props.setAttributes(o); }; };

      var controls = el(IC, {},
        el(c.PanelBody, { title: __('Project details', 'pressroot'), initialOpen: true },
          el(c.TextControl, { label: __('Title', 'pressroot'), value: a.heading, onChange: set('heading') }),
          el(c.TextareaControl, { label: __('Excerpt', 'pressroot'), value: a.excerpt, rows: 3, onChange: set('excerpt') }),
          el(c.TextControl, { label: __('Tags (comma-separated)', 'pressroot'), value: a.tags, placeholder: 'React, Tailwind, PHP', onChange: set('tags') })
        ),
        el(c.PanelBody, { title: __('Image', 'pressroot'), initialOpen: false },
          el(c.TextControl, { label: __('Image URL', 'pressroot'), value: a.imageUrl, type: 'url', onChange: set('imageUrl') }),
          el(c.TextControl, { label: __('Alt text', 'pressroot'), value: a.imageAlt, onChange: set('imageAlt') })
        ),
        el(c.PanelBody, { title: __('Links', 'pressroot'), initialOpen: false },
          el(c.TextControl, { label: __('Primary link (overrides both)', 'pressroot'), value: a.link, type: 'url', onChange: set('link') }),
          el(c.TextControl, { label: __('Live site URL', 'pressroot'), value: a.liveUrl, type: 'url', onChange: set('liveUrl') }),
          el(c.TextControl, { label: __('GitHub URL', 'pressroot'), value: a.githubUrl, type: 'url', onChange: set('githubUrl') })
        )
      );
      return el(Fragment, {}, controls, el('div', ubp ? ubp() : {}, el(SSR, {
        block: 'prt/project-card',
        attributes: a,
        LoadingResponsePlaceholder: function () { return skeleton(a); },
        EmptyResponsePlaceholder: function () { return skeleton(a); }
      })));
    },
    save: function () { return null; }
  });
})(window.wp);
