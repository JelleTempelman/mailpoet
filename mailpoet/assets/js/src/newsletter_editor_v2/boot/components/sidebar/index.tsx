import { ComponentProps } from 'react';
import { Platform } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { cog } from '@wordpress/icons';
import { ComplementaryArea } from '@wordpress/interface';

const sidebarActiveByDefault = Platform.select({
  web: true,
  native: false,
});

type Props = ComponentProps<typeof ComplementaryArea>;

export function Sidebar(props: Props): JSX.Element {
  return (
    <ComplementaryArea
      identifier="xxx"
      header={<div>Sidebar</div>}
      closeLabel={__('Close settings')}
      headerClassName="edit-post-sidebar__panel-tabs"
      title={__('Settings')}
      icon={cog}
      className="edit-post-sidebar"
      panelClassName="edit-post-sidebar"
      smallScreenTitle={__('(no title)')}
      scope="sid1"
      isActiveByDefault={sidebarActiveByDefault}
      showIconLabels={false}
      {...props}
    >
      <div>Content</div>
    </ComplementaryArea>
  );
}
