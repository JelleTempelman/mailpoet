import { registerBlockType } from '@wordpress/blocks';
import { registerButton } from './blocks/button';
import { registerColumns } from './blocks/columns';
import { registerColumn } from './blocks/column';
import { registerLink } from './blocks/social_link';
import { registerSpacer } from './blocks/spacer';

import {
  name as todoBlockName,
  settings as todoBlockSettings,
} from './blocks/todo';

import {
  name as footerBlockName,
  settings as footerBlockSettings,
} from './blocks/footer';

import {
  name as headerBlockName,
  settings as headerBlockSettings,
} from './blocks/header';

// Register Blocks and modifications
registerButton();
registerColumns();
registerColumn();
registerSpacer();
registerLink();

// Add Custom Block Type
registerBlockType(headerBlockName, headerBlockSettings);
registerBlockType(footerBlockName, footerBlockSettings);
registerBlockType(todoBlockName, todoBlockSettings);
