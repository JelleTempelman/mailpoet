import { fetchLinkSuggestions } from 'form_editor/utils/link_suggestions';
import { SETTINGS_DEFAULTS } from '@wordpress/block-editor';
import { uploadMedia } from '@wordpress/media-utils';
import { select } from '@wordpress/data';

export const getEditorSettings = () => ({
  ...SETTINGS_DEFAULTS,
  ...window.mailpoet_email_editor_settings,
  mediaUpload: select('core').canUser('create', 'media', '')
    ? uploadMedia
    : null,
  maxWidth: 580, // Force max width for emails - we may later introduce multiple widths
  hasUploadPermissions: select('core').canUser('create', 'media', ''),
  template: null,
  templateLock: false,
  reusableBlocks: [],
  fetchLinkSuggestions,
  __experimentalBlockPatterns: [], // we don't want patterns in our inserter
  __experimentalBlockPatternCategories: [],
});
