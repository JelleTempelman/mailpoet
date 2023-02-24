<?php declare(strict_types = 1);

namespace MailPoet\Automation\Integrations\AutomateWoo;

use AutomateWoo\Triggers;
use AutomateWoo\Triggers\AbstractBatchedDailyTrigger;
use MailPoet\Automation\Engine\Data\Automation;
use MailPoet\Automation\Engine\Storage\AutomationStorage;
use MailPoet\Automation\Engine\WordPress;

class BatchedDailyTriggerHandler {
  /** @var AutomationStorage */
  private $automationStorage;

  /** @var WordPress */
  private $wordPress;

  public function __construct(
    AutomationStorage $automationStorage,
    WordPress $wordPress
  ) {
    $this->automationStorage = $automationStorage;
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
    $processed = 0;
    do {
      $batch = $awTrigger->get_batch_for_workflow($workflow, $processed, 10);
      foreach ($batch as $item) {
        // TODO: get subscriber from item, create MP automation workflow run
        $processed++;
      }
    } while (count($batch) > 0);
  }
}
