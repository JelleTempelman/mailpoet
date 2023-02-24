<?php declare(strict_types = 1);

namespace MailPoet\Automation\Integrations\AutomateWoo;

use AutomateWoo\Triggers;
use AutomateWoo\Triggers\AbstractBatchedDailyTrigger;
use AutomateWoo\Workflow;
use MailPoet\Automation\Engine\Integration;
use MailPoet\Automation\Engine\Registry;
use MailPoet\Automation\Integrations\AutomateWoo\Triggers\AutomateWooTrigger;

class AutomateWooIntegration implements Integration {
  /** @var AutomateWooTrigger  */
  private $automateWooTrigger;

  public function __construct(
    AutomateWooTrigger $automateWooTrigger,
  ) {
    $this->automateWooTrigger = $automateWooTrigger;
  }

  public function register(Registry $registry): void {
    $registry->addTrigger($this->automateWooTrigger);
  }
}
