<?php declare(strict_types = 1);

namespace MailPoet\Automation\Integrations\AutomateWoo;

use AutomateWoo\Triggers;
use AutomateWoo\Triggers\AbstractBatchedDailyTrigger;
use MailPoet\Automation\Engine\Control\ActionScheduler;
use MailPoet\Automation\Engine\Data\Automation;
use MailPoet\Automation\Engine\Data\Step;

class BatchedDailyTriggerScheduler {
  public const HOOK = 'mailpoet/automation/automatewoo_batched_daily_trigger';

  /** @var ActionScheduler */
  private $actionScheduler;

  public function __construct(
    ActionScheduler $actionScheduler
  ) {
    $this->actionScheduler = $actionScheduler;
  }

  public function processTrigger(Step $step, Automation $automation): void {
    if ($automation->getStatus() !== Automation::STATUS_ACTIVE) {
      return;
    }

    $trigger = Triggers::get($step->getArgs()['aw_trigger']);
    if (!$trigger instanceof AbstractBatchedDailyTrigger) {
      return;
    }

    // unschedule previous action and schedule a new one
    $actionArgs = ['automation_id' => $automation->getId(), 'trigger_id' => $step->getId()];
    $this->actionScheduler->unscheduleAction(self::HOOK, $actionArgs);
    $this->actionScheduler->scheduleRecurring(strtotime('tomorrow'), DAY_IN_SECONDS, self::HOOK, $actionArgs, false);
    // TODO: actual time of day
    // TODD: actions cleanup... we can do it runtime, I guess (when active workflow not found)
    // TODO: should we save automation version ID as well?
  }
}
