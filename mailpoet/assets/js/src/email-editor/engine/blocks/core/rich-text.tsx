import { BlockControls } from '@wordpress/block-editor';
import { ToolbarGroup, ToolbarButton } from '@wordpress/components';
import { registerFormatType, unregisterFormatType, insert, create } from '@wordpress/rich-text';

/**
 * Disable Rich text formats we currently cannot support
 * Note: This will remove its support for all blocks in the email editor e.g., p, h1,h2, etc
 */
function disableCertainRichTextFormats() {
  // remove support for inline image - We can't use it
  unregisterFormatType('core/image');

  // remove support for Inline code - Not well formatted
  unregisterFormatType('core/code');

  // remove support for Language - Not supported for now
  unregisterFormatType('core/language');
}

function initPersonalizedTags() {
  registerFormatType('mailpoet-email-editor/personalized-tag-inserter', {
    name: 'mailpoet-email-editor/personalized-tag-inserter',
    title: 'Insert Subscriber First Name',
    tagName: 'div',  // This tagName is necessary but irrelevant for a button
    className: null,
    interactive: true,

    edit({isActive, value, onChange}) {
      return (
        <BlockControls>
          <ToolbarGroup>
            <ToolbarButton
              icon="editor-code"
              title="Sample output"
              onClick={() => {
                onChange(
                  insert(value, create({
                    html: '<span class="mailpoet-personalized-tag" style="background: yellowgreen;">&lt;//wp:mailpoet:subscriber-firstname&gt;</span>',
                })));
              }}
              isActive={isActive}
            />
          </ToolbarGroup>
        </BlockControls>
      );
    },
  });
}

export {
  disableCertainRichTextFormats,
  initPersonalizedTags
};
