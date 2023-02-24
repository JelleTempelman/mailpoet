<?php declare(strict_types = 1);

namespace MailPoet\Automation\Integrations\AutomateWoo;

use AutomateWoo\Triggers;
use AutomateWoo\Triggers\AbstractBatchedDailyTrigger;
use MailPoet\Automation\Engine\Data\Automation;
use MailPoet\Automation\Engine\Data\Subject;
use MailPoet\Automation\Engine\Hooks;
use MailPoet\Automation\Engine\Storage\AutomationStorage;
use MailPoet\Automation\Engine\WordPress;
use MailPoet\Automation\Integrations\AutomateWoo\Triggers\AutomateWooTrigger;
use MailPoet\Automation\Integrations\MailPoet\Subjects\SubscriberSubject;
use MailPoet\Subscribers\SubscribersRepository;
use WC_Payment_Tokens;

class BatchedDailyTriggerHandler {
  /** @var AutomateWooTrigger */
  private $trigger;

  /** @var AutomationStorage */
  private $automationStorage;

  /** @var SubscribersRepository */
  private $subscribersRepository;

  /** @var WordPress */
  private $wordPress;

  public function __construct(
    AutomateWooTrigger $trigger,
    AutomationStorage $automationStorage,
    SubscribersRepository $subscribersRepository,
    WordPress $wordPress
  ) {
    $this->trigger = $trigger;
    $this->automationStorage = $automationStorage;
    $this->subscribersRepository = $subscribersRepository;
    $this->wordPress = $wordPress;
  }

  public function initialize(): void {
    $this->wordPress->addAction(BatchedDailyTriggerScheduler::HOOK, [$this, 'handleTrigger']);
  }

  public function handleTrigger(array $args): void {
    $automationId = $args['automation_id'] ?? null;
    $triggerId = $args['trigger_id'] ?? null;
    if (!$automationId || !$triggerId) {
      return;
    }

    $automation = $this->automationStorage->getAutomation($automationId);
    if (!$automation || $automation->getStatus() !== Automation::STATUS_ACTIVE) {
      return;
    }

    $trigger = $automation->getStep($triggerId);
    if (!$trigger) {
      return;
    }

    $awTriggerName = $trigger->getArgs()['aw_trigger'];
    if (!$awTriggerName) {
      return;
    }

    $awTrigger = Triggers::get($awTriggerName);
    if (!$awTrigger instanceof AbstractBatchedDailyTrigger) {
      return;
    }

    $workflow = new InMemoryWorkflow();
    $workflow->set_trigger_data($awTriggerName, [
      'days_before_expiry' => 4, // TODO: this is hardcoded and specific for customer_before_saved_card_expiry
    ]);

    $processed = 0;
    do {
      $batch = $awTrigger->get_batch_for_workflow($workflow, $processed, 10);
      foreach ($batch as $item) {
        // TODO: this is specific for customer_before_saved_card_expiry
        $tokenId = (int)$item['token'];
        $userId = WC_Payment_Tokens::get($tokenId)->get_user_id();
        $subscriber = $this->subscribersRepository->findOneBy(['wpUserId' => $userId]);

        $this->wordPress->doAction(Hooks::TRIGGER, $this->trigger, [
          new Subject(SubscriberSubject::KEY, ['subscriber_id' => $subscriber->getId()]),
        ]);
        $processed++;
      }
    } while (count($batch) > 0);
  }
}
