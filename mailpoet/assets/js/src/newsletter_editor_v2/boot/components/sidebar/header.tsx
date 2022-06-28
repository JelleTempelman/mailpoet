import { Button } from '@wordpress/components';
import { useDispatch } from '@wordpress/data';
import { blockSidebarKey, store, emailSidebarKey } from '../../store';

// See: https://github.com/WordPress/gutenberg/blob/9601a33e30ba41bac98579c8d822af63dd961488/packages/edit-post/src/components/sidebar/settings-header/index.js

type Props = {
  sidebarKey: string;
};

export function Header({ sidebarKey }: Props): JSX.Element {
  const { openSidebar } = useDispatch(store);
  const openBlockSettings = () => openSidebar(blockSidebarKey);
  const openEmailSettings = () => openSidebar(emailSidebarKey);

  const [workflowAriaLabel, workflowActiveClass] =
    sidebarKey === blockSidebarKey
      ? ['Block (selected)', 'is-active']
      : ['Block', ''];

  const [stepAriaLabel, stepActiveClass] =
    sidebarKey === emailSidebarKey
      ? ['Email (selected)', 'is-active']
      : ['Email', ''];

  return (
    <ul>
      <li>
        <Button
          onClick={openBlockSettings}
          className={`edit-post-sidebar__panel-tab ${workflowActiveClass}`}
          aria-label={workflowAriaLabel}
          data-label="Workflow"
        >
          Workflow
        </Button>
      </li>
      <li>
        <Button
          onClick={openEmailSettings}
          className={`edit-post-sidebar__panel-tab ${stepActiveClass}`}
          aria-label={stepAriaLabel}
          data-label="Workflow"
        >
          Step
        </Button>
      </li>
    </ul>
  );
}
