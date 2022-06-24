import classnames from 'classnames';
import { Popover, SlotFillProvider } from '@wordpress/components';
import {
  ComplementaryArea,
  InterfaceSkeleton,
  FullscreenMode,
} from '@wordpress/interface';
import { ShortcutProvider } from '@wordpress/keyboard-shortcuts';
import { Header } from './components/header';
import { Sidebar } from './components/sidebar/index';

// See: https://github.com/WordPress/gutenberg/blob/9601a33e30ba41bac98579c8d822af63dd961488/packages/edit-post/src/components/layout/index.js

export function Editor(): JSX.Element {
  const className = classnames(
    'edit-post-layout',
    'interface-interface-skeleton',
    {
      'is-sidebar-opened': true,
      'show-icon-labels': true,
    },
  );

  return (
    <ShortcutProvider>
      <SlotFillProvider>
        <FullscreenMode isActive={false} />
        <Sidebar />
        <InterfaceSkeleton
          className={className}
          header={<Header />}
          content={<h1>Hello</h1>}
          sidebar={<ComplementaryArea.Slot scope="ss" />}
          secondarySidebar={false ? <div>Something</div> : null}
        />
        <Popover.Slot />
      </SlotFillProvider>
    </ShortcutProvider>
  );
}
