// eslint-disable-next-line @typescript-eslint/no-unused-vars
// @ts-expect-error No types available for this component
import { BlockPreview } from '@wordpress/block-editor';
import { store as editorStore } from '@wordpress/editor';
import { dispatch } from '@wordpress/data';
import { Modal } from '@wordpress/components';
import { Async } from 'email-editor/engine/components/sidebar/template-select/async';
import { getTemplatesForPreview } from './templates-data';

export function SelectTemplateModal({ isOpen, setIsOpen }) {
  if (!isOpen) {
    return null;
  }
  const templates = getTemplatesForPreview();

  const handleTemplateSelection = (template) => {
    setIsOpen(false);
    void dispatch(editorStore).resetEditorBlocks(template.patternParsed);
  };

  return (
    // eslint-disable-next-line @typescript-eslint/no-unsafe-return
    <Modal title="Select a template" onRequestClose={() => setIsOpen(false)}>
      {templates.map((template) => (
        <div key={template.slug}>
          {/* eslint-disable-next-line @typescript-eslint/restrict-template-expressions */}
          <h2>{`Template ${template.slug}`}</h2>
          <div
            role="button"
            tabIndex={0}
            style={{
              width: '450px',
              border: '1px solid #000',
              padding: '20px',
              display: 'block',
              cursor: 'pointer',
            }}
            onClick={() => {
              handleTemplateSelection(template);
            }}
            onKeyPress={(event) => {
              if (event.key === 'Enter' || event.key === ' ') {
                handleTemplateSelection(template);
              }
            }}
          >
            <Async placeholder={<p>rendering template</p>}>
              <BlockPreview
                blocks={template.contentParsed}
                viewportWidth={1200}
              />
            </Async>
          </div>
        </div>
      ))}
    </Modal>
  );
}
