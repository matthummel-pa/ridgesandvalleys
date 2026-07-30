/**
 * Block editor entry — registers Ridges & Valleys custom blocks (TypeScript).
 *
 * Dynamic blocks: the edit component lives here; the front-end markup is
 * rendered server-side in app/blocks.php. save() returns null.
 */
import { registerBlockType } from '@wordpress/blocks';
import { createElement as el, Fragment } from '@wordpress/element';
import {
  useBlockProps,
  RichText,
  InspectorControls,
} from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

interface RidgelineCtaAttributes {
  heading: string;
  text: string;
  buttonLabel: string;
  buttonUrl: string;
}

registerBlockType<RidgelineCtaAttributes>('rv/ridgeline-cta', {
  edit: ({ attributes, setAttributes }) => {
    const { heading, text, buttonLabel, buttonUrl } = attributes;
    const blockProps = useBlockProps({
      className: 'rv-cta-band alignfull',
      style: { padding: '3.5rem 1.5rem', textAlign: 'center', color: '#fff' },
    });

    return el(Fragment, null, [
      el(
        InspectorControls,
        { key: 'inspector' },
        el(
          PanelBody,
          { title: __('Button', 'sage'), initialOpen: true },
          el(TextControl, {
            label: __('Button label', 'sage'),
            value: buttonLabel,
            onChange: (value: string) => setAttributes({ buttonLabel: value }),
          }),
          el(TextControl, {
            label: __('Button URL', 'sage'),
            value: buttonUrl,
            onChange: (value: string) => setAttributes({ buttonUrl: value }),
          }),
        ),
      ),
      el('section', { ...blockProps, key: 'preview' }, [
        el(RichText, {
          key: 'heading',
          tagName: 'h2',
          className: 'rv-cta-title',
          value: heading,
          allowedFormats: [],
          onChange: (value: string) => setAttributes({ heading: value }),
          placeholder: __('Heading…', 'sage'),
        }),
        el(RichText, {
          key: 'text',
          tagName: 'p',
          className: 'rv-cta-sub',
          value: text,
          allowedFormats: [],
          onChange: (value: string) => setAttributes({ text: value }),
          placeholder: __('Supporting line…', 'sage'),
        }),
        el(
          'span',
          { key: 'btn', className: 'rv-btn rv-btn-on-dark' },
          buttonLabel || __('Get a quote', 'sage'),
        ),
      ]),
    ]);
  },
  save: () => null,
});
