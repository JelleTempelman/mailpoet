import { __, _x } from '@wordpress/i18n';
import { commentAuthorAvatar } from '@wordpress/icons';
import { StepType } from '../../../../editor/store';
import { Edit } from './edit';

export const step: StepType = {
  key: 'automate-woo:trigger',
  group: 'triggers',
  title: __('AutomateWoo', 'mailpoet'),
  foreground: '#2271b1',
  background: '#f0f6fc',
  description: __(
    'Starts the automation with a AutomateWoo trigger.',
    'mailpoet',
  ),
  subtitle: () => _x('Trigger', 'noun', 'mailpoet'),
  icon: () => (
    <div style={{ width: '100%', height: '100%', scale: '1.4' }}>
      {commentAuthorAvatar}
    </div>
  ),
  edit: () => <Edit />,
} as const;
