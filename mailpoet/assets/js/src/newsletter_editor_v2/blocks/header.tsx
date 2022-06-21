import { useBlockProps, InnerBlocks } from '@wordpress/block-editor';
import { BlockConfiguration, TemplateArray } from '@wordpress/blocks';

export const name = 'mailpoet/header';

const headerTemplate: TemplateArray = [
  [
    'core/paragraph',
    {
      content: '<a href="[link:view_in_browser]">View in Browser</a>',
    },
  ],
  ['core/paragraph', { content: 'Add your address' }],
];

export const settings: BlockConfiguration = {
  title: 'Email Header',
  apiVersion: 2,
  description: 'Email Header Content',
  category: 'text',
  attributes: {},
  supports: {
    html: false,
    multiple: true,
  },
  edit: function Edit() {
    const blockProps = useBlockProps();
    return (
      <div {...blockProps}>
        <InnerBlocks
          allowedBlocks={['core/paragraph']}
          template={headerTemplate}
          templateLock={false}
        />
      </div>
    );
  },
  save: function Save() {
    return <InnerBlocks.Content />;
  },
};
