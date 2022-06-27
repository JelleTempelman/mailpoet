import {
  BlockEditorProvider,
  BlockList,
  WritingFlow,
  ObserveTyping,
  BlockTools,
} from '@wordpress/block-editor';
import { Popover } from '@wordpress/components';
import { useState } from '@wordpress/element';

export function VisualEditor() {
  const [blocks, updateBlocks] = useState([]);

  return (
    <BlockEditorProvider
      value={blocks}
      onInput={(blcks) => updateBlocks(blcks)}
      onChange={(blcks) => updateBlocks(blcks)}
    >
      <BlockTools>
        <WritingFlow>
          <ObserveTyping>
            <BlockList />
          </ObserveTyping>
        </WritingFlow>
        <Popover.Slot />
      </BlockTools>
    </BlockEditorProvider>
  );
}
