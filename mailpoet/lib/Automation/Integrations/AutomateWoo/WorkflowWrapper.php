<?php
namespace MailPoet\Automation\Integrations\AutomateWoo;

use AutomateWoo\Data_Layer;
use AutomateWoo\Workflow as AWWorkflow;
use AutomateWoo\Trigger as AWTrigger;
use MailPoet\Automation\Engine\Data\Automation;
use MailPoet\Automation\Engine\Data\Subject;
use MailPoet\Automation\Engine\Hooks;
use MailPoet\Automation\Engine\Storage\AutomationStorage;
use MailPoet\Automation\Integrations\AutomateWoo\Triggers\AutomateWooTrigger;
use MailPoet\Automation\Integrations\MailPoet\Subjects\SubscriberSubject;
use MailPoet\Subscribers\SubscribersRepository;
use MailPoet\WP\Functions;

class WorkflowWrapper extends AWWorkflow
{

  private $automationStorage;
  private $subscribersRepository;
  private $wp;
  private $currentAWTrigger;
  private $trigger;

  public function __construct(
    AutomationStorage $automationStorage,
    SubscribersRepository $subscribersRepository,
    Functions $wp,
    AWTrigger $currentAWTrigger,
    AutomateWooTrigger $trigger
  ) {
    $this->automationStorage = $automationStorage;
    $this->subscribersRepository = $subscribersRepository;
    $this->wp = $wp;
    $this->currentAWTrigger = $currentAWTrigger;
    $this->trigger = $trigger;
  }

  public function maybe_run($dataLayer, $skipValidation = false, $forceImmediate = false) {
    $automations = $this->findAutomations();
    if (! $automations) {
      return;
    }
    if ( ! is_a( $dataLayer, 'AutomateWoo\Data_Layer' ) ) {
      $dataLayer = new Data_Layer( $dataLayer );
    }
    $email = $dataLayer->get_customer_email();
    if (! $email) {
      return;
    }

    $subscriber = $this->subscribersRepository->findOneBy(['email' => $email]);
    if (! $subscriber) {
      return;
    }
    $this->wp->doAction(Hooks::TRIGGER, $this->trigger, [
      new Subject(SubscriberSubject::KEY, ['subscriber_id' => $subscriber->getId()]),
    ]);
  }

  /**
   * @return Automation[]
   */
  private function findAutomations() : array {
    $automations = $this->automationStorage->getActiveAutomationsByTrigger($this->trigger);
    return array_filter(
      $automations,
      function (Automation $automation) {
        return $automation->getTrigger($this->trigger->getKey()) !== null
          && $automation->getTrigger($this->trigger->getKey())->getArgs()['aw_trigger'] === $this->currentAWTrigger->get_name();
      }
    );
  }
}
