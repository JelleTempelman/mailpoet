import { Button, NavigableMenu } from '@wordpress/components';
import { PinnedItems } from '@wordpress/interface';

export function Header(): JSX.Element {
  return (
    <div className="edit-post-header">
      <div className="edit-post-header__toolbar">
        <NavigableMenu
          className="edit-post-header-toolbar"
          orientation="horizontal"
          role="toolbar"
        >
          <div className="edit-post-header-toolbar__left"></div>
        </NavigableMenu>
      </div>
      <div className="edit-post-header__settings">
        <Button variant="tertiary">Save Draft</Button>
        <Button variant="primary" className="editor-post-publish-button">
          Publish
        </Button>
        <PinnedItems.Slot scope="ss" />
      </div>
    </div>
  );
}
