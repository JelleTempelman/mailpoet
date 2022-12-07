import PropTypes from 'prop-types';
import { useCallback, useEffect, useState } from 'react';
import { partial } from 'underscore';

import { MailPoet } from 'mailpoet';
import { WelcomeWizardSenderStep } from './steps/sender_step.jsx';
import { WelcomeWizardUsageTrackingStep } from './steps/usage_tracking_step.jsx';
import { WelcomeWizardPitchMSSStep } from './steps/pitch_mss_step.jsx';
import { WooCommerceController } from './woocommerce_controller';
import { WelcomeWizardStepLayout } from './layout/step_layout.jsx';

import { createSenderSettings } from './create_sender_settings.jsx';
import {
  getStepsCount,
  mapStepNumberToStepName,
  redirectToNextStep,
} from './steps_numbers.jsx';
import { Steps } from '../common/steps/steps';
import { StepsContent } from '../common/steps/steps_content';
import { TopBar } from '../common/top_bar/top_bar';
import { ErrorBoundary } from '../common';

function WelcomeWizardStepsController(props) {
  const stepsCount = getStepsCount();
  const step = parseInt(props.match.params.step, 10);

  const [loading, setLoading] = useState(false);
  const [sender, setSender] = useState(window.sender_data);

  useEffect(() => {
    if (step > stepsCount || step < 1) {
      props.history.push('/steps/1');
    }
  }, [step, stepsCount, props.history]);

  function updateSettings(data) {
    setLoading(true);
    return MailPoet.Ajax.post({
      api_version: window.mailpoet_api_version,
      endpoint: 'settings',
      action: 'set',
      data,
    })
      .then(() => setLoading(false))
      .fail((response) => {
        setLoading(false);
        if (response.errors.length > 0) {
          MailPoet.Notice.error(
            response.errors.map((error) => error.message),
            { scroll: true },
          );
        }
      });
  }

  function finishWizard() {
    updateSettings({
      version: window.mailpoet_version,
    }).then(() => {
      window.location = window.finish_wizard_url;
    });
  }

  const redirect = partial(redirectToNextStep, props.history, finishWizard);

  const submitTracking = useCallback(
    (tracking, libs3rdParty) => {
      setLoading(true);
      updateSettings({
        analytics: { enabled: tracking ? '1' : '' },
        '3rd_party_libs': { enabled: libs3rdParty ? '1' : '' },
      }).then(() => redirect(step));
    },
    [redirect, step],
  );

  const updateSender = useCallback(
    (data) => {
      setSender({ ...sender, ...data });
    },
    [sender],
  );

  const submitSender = useCallback(() => {
    updateSettings(createSenderSettings(sender)).then(() => redirect(step));
  }, [redirect, sender, step]);

  const skipSenderStep = useCallback(
    (e) => {
      e.preventDefault();
      setLoading(true);
      updateSettings(
        createSenderSettings({ address: window.admin_email, name: '' }),
      ).then(() => {
        redirect(step);
      });
    },
    [redirect, step],
  );

  const stepName = mapStepNumberToStepName(step);

  return (
    <>
      <TopBar logoWithLink={false}>
        <Steps count={stepsCount} current={step} />
      </TopBar>
      <StepsContent>
        {stepName === 'WelcomeWizardSenderStep' ? (
          <WelcomeWizardStepLayout
            illustrationUrl={window.wizard_sender_illustration_url}
          >
            <ErrorBoundary>
              <WelcomeWizardSenderStep
                update_sender={updateSender}
                submit_sender={submitSender}
                skipStep={skipSenderStep}
                loading={loading}
                sender={sender}
              />
            </ErrorBoundary>
          </WelcomeWizardStepLayout>
        ) : null}

        {stepName === 'WelcomeWizardUsageTrackingStep' ? (
          <WelcomeWizardStepLayout
            illustrationUrl={window.wizard_tracking_illustration_url}
          >
            <ErrorBoundary>
              <WelcomeWizardUsageTrackingStep
                loading={loading}
                submitForm={submitTracking}
              />
            </ErrorBoundary>
          </WelcomeWizardStepLayout>
        ) : null}

        {stepName === 'WelcomeWizardPitchMSSStep' ? (
          <WelcomeWizardStepLayout
            illustrationUrl={window.wizard_MSS_pitch_illustration_url}
          >
            <ErrorBoundary>
              <WelcomeWizardPitchMSSStep
                next={() => redirect(step)}
                subscribersCount={window.mailpoet_subscribers_count}
                mailpoetAccountUrl={window.mailpoet_account_url}
                purchaseUrl={MailPoet.MailPoetComUrlFactory.getPurchasePlanUrl(
                  MailPoet.subscribersCount,
                  MailPoet.currentWpUserEmail,
                  'business',
                  { utm_medium: 'onboarding', utm_campaign: 'purchase' },
                )}
              />
            </ErrorBoundary>
          </WelcomeWizardStepLayout>
        ) : null}

        {stepName === 'WizardWooCommerceStep' ? (
          <ErrorBoundary>
            <WooCommerceController
              isWizardStep
              redirectToNextStep={() => redirect(step)}
            />
          </ErrorBoundary>
        ) : null}
      </StepsContent>
    </>
  );
}

WelcomeWizardStepsController.propTypes = {
  match: PropTypes.shape({
    params: PropTypes.shape({
      step: PropTypes.string,
    }).isRequired,
  }).isRequired,
  history: PropTypes.shape({
    push: PropTypes.func.isRequired,
  }).isRequired,
};
WelcomeWizardStepsController.displayName = 'WelcomeWizardStepsController';
export { WelcomeWizardStepsController };
