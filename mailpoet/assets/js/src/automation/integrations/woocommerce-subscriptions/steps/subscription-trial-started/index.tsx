import { __ } from '@wordpress/i18n';
import { StepType } from '../../../../editor/store';
import { Icon } from './icon';
import { PremiumModalForStepEdit } from '../../../../../common/premium-modal';

const keywords = [
  __('woocommerce', 'mailpoet'),
  __('subscription', 'mailpoet'),
  __('trial', 'mailpoet'),
  __('started', 'mailpoet'),
];
export const step: StepType = {
  key: 'woocommerce-subscriptions:trial-started',
  group: 'triggers',
  title: () => __('Woo Subscription trial started', 'mailpoet'),
  description: () =>
    __('Start the automation when a subscription trial started.', 'mailpoet'),

  subtitle: () => __('Trigger', 'mailpoet'),
  keywords,
  foreground: '#2271b1',
  background: '#f0f6fc',
  icon: () => <Icon />,
  edit: () => (
    <PremiumModalForStepEdit
      tracking={{
        utm_medium: 'upsell_modal',
        utm_campaign: 'create_automation_editor_woo_subscription_trial_ended',
      }}
    >
      {__(
        'Starting an automation when a subscription trial started is a premium feature.',
        'mailpoet',
      )}
    </PremiumModalForStepEdit>
  ),
} as const;