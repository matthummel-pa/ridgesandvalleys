/* AI content generation in the block editor.
 * Adds a "Generate with AI" toolbar button to paragraph/heading/list-item
 * blocks. Clicking opens a small popover: pick a model (Pollinations plus
 * any connector configured on Appearance -> AI Connectors), describe what
 * the block should say, and its content is replaced with the result. Calls
 * the prt_ai_generate_block_content AJAX endpoint (app/ai-content-block.php)
 * — no API key is ever present in this file or in the browser.
 */
(function (wp) {
  if (!wp || !wp.hooks || !wp.compose || !wp.blockEditor) return;

  var el = wp.element.createElement;
  var useState = wp.element.useState;
  var Fragment = wp.element.Fragment;
  var __ = wp.i18n.__;
  var BlockControls = wp.blockEditor.BlockControls;
  var c = wp.components;

  var TARGET_BLOCKS = ['core/paragraph', 'core/heading', 'core/list-item'];

  function AIToolbarButton(props) {
    var openState   = useState(false);
    var isOpen      = openState[0], setOpen = openState[1];
    var promptState = useState('');
    var prompt      = promptState[0], setPrompt = promptState[1];
    var models      = (window.prtAIBlock && window.prtAIBlock.models) || [];
    var modelState  = useState(models.length ? models[0].slug : 'pollinations');
    var model       = modelState[0], setModel = modelState[1];
    var busyState   = useState(false);
    var busy        = busyState[0], setBusy = busyState[1];
    var noteState   = useState('');
    var note        = noteState[0], setNote = noteState[1];

    function generate() {
      if (!prompt.trim()) {
        setNote(__('Describe what this should say.', 'pressroot'));
        return;
      }
      if (!window.prtAIBlock) {
        setNote(__('Not available on this screen.', 'pressroot'));
        return;
      }
      setBusy(true);
      setNote(__('Generating…', 'pressroot'));

      var body = new URLSearchParams();
      body.set('action', 'prt_ai_generate_block_content');
      body.set('nonce', window.prtAIBlock.nonce);
      body.set('prompt', prompt);
      body.set('model', model);

      fetch(window.prtAIBlock.ajaxUrl, { method: 'POST', credentials: 'same-origin', body: body })
        .then(function (r) { return r.json(); })
        .then(function (json) {
          setBusy(false);
          if (!json.success) {
            setNote((json.data && json.data.message) || __('Generation failed.', 'pressroot'));
            return;
          }
          props.setAttributes({ content: (json.data.text || '').trim() });
          setOpen(false);
          setNote('');
          setPrompt('');
        })
        .catch(function () {
          setBusy(false);
          setNote(__('Generation failed — check your connection.', 'pressroot'));
        });
    }

    return el(Fragment, {},
      el(BlockControls, {},
        el(c.ToolbarGroup, {},
          el(c.ToolbarButton, {
            icon: 'lightbulb',
            label: __('Generate with AI', 'pressroot'),
            onClick: function () { setOpen(!isOpen); }
          })
        )
      ),
      isOpen ? el(c.Popover, { onClose: function () { setOpen(false); }, placement: 'bottom-start' },
        el('div', { style: { padding: 16, width: 280 } },
          models.length ? el(c.SelectControl, {
            label: __('Model', 'pressroot'),
            value: model,
            options: models.map(function (m) { return { label: m.label, value: m.slug }; }),
            onChange: setModel
          }) : null,
          el(c.TextareaControl, {
            label: __('What should this say?', 'pressroot'),
            value: prompt,
            rows: 3,
            onChange: setPrompt,
            placeholder: __('e.g. a short paragraph about our 24/7 support', 'pressroot')
          }),
          el(c.Button, { variant: 'primary', isBusy: busy, disabled: busy, onClick: generate }, __('Generate & replace', 'pressroot')),
          note ? el('p', { style: { fontSize: 12, color: '#646970', marginTop: 8 } }, note) : null
        )
      ) : null
    );
  }

  var withAIContentControl = wp.compose.createHigherOrderComponent(function (BlockEdit) {
    return function (props) {
      if (TARGET_BLOCKS.indexOf(props.name) === -1 || !props.isSelected) {
        return el(BlockEdit, props);
      }
      return el(Fragment, {}, el(BlockEdit, props), el(AIToolbarButton, props));
    };
  }, 'withAIContentControl');

  wp.hooks.addFilter('editor.BlockEdit', 'pressroot/ai-content-generate', withAIContentControl);
})(window.wp);
