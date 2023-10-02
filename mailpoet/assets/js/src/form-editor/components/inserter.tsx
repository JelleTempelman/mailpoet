import { useSelect } from '@wordpress/data';
import { close } from '@wordpress/icons';
import { Button } from '@wordpress/components';
import { __experimentalLibrary as Library } from '@wordpress/block-editor';
import { storeName } from '../store';

type Props = {
  setIsInserterOpened: (boolean) => void;
};

export function Inserter({ setIsInserterOpened }: Props): JSX.Element {
  const insertPoint = useSelect(
    (sel) => sel(storeName).getInserterPanelInsertPoint(),
    [],
  );
  return (
    <div className="edit-post-editor__inserter-panel">
      <div className="edit-post-editor__inserter-panel-header">
        <Button icon={close} onClick={(): void => setIsInserterOpened(false)} />
      </div>
      <div className="edit-post-editor__inserter-panel-content">
        <Library
          showMostUsedBlocks
          showInserterHelpPanel={false}
          rootClientId={insertPoint.rootClientId ?? undefined}
          __experimentalInsertionIndex={insertPoint.insertionIndex ?? undefined}
        />
      </div>
    </div>
  );
}