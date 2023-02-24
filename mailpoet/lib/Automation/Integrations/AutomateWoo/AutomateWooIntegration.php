<?php declare(strict_types = 1);

namespace MailPoet\Automation\Integrations\AutomateWoo;

use MailPoet\Automation\Engine\Integration;
use MailPoet\Automation\Engine\Registry;
use MailPoet\Automation\Integrations\AutomateWoo\Triggers\AutomateWooTrigger;

class AutomateWooIntegration implements Integration {
  /** @var AutomateWooTrigger  */
  private $automateWooTrigger;

  /** @var BatchedDailyTriggerScheduler */
  private $batchedDailyTriggerScheduler;

  /** @var BatchedDailyTriggerHandler */
  private $batchedDailyTriggerHandler;

  public function __construct(
    AutomateWooTrigger $automateWooTrigger,
    BatchedDailyTriggerScheduler $batchedDailyTriggerScheduler,
    BatchedDailyTriggerHandler $batchedDailyTriggerHandler
  ) {
    $this->automateWooTrigger = $automateWooTrigger;
    $this->batchedDailyTriggerScheduler = $batchedDailyTriggerScheduler;
    $this->batchedDailyTriggerHandler = $batchedDailyTriggerHandler;
  }

  public function register(Registry $registry): void {
    $registry->addTrigger($this->automateWooTrigger);

    $registry->onBeforeAutomationStepSave(
      [$this->batchedDailyTriggerScheduler, 'processTrigger'],
      $this->automateWooTrigger->getKey()
    );

    $this->batchedDailyTriggerHandler->initialize();
  }
}
