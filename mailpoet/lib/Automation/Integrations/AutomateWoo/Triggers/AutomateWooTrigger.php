<?php

namespace MailPoet\Automation\Integrations\AutomateWoo\Triggers;

use MailPoet\Automation\Engine\Data\StepRunArgs;
use MailPoet\Automation\Engine\Data\StepValidationArgs;
use MailPoet\Automation\Engine\Integration\Trigger;
use MailPoet\Automation\Engine\Storage\AutomationStorage;
use MailPoet\Automation\Integrations\AutomateWoo\WorkflowWrapper;
use MailPoet\Automation\Integrations\MailPoet\Subjects\SubscriberSubject;
use MailPoet\Subscribers\SubscribersRepository;
use MailPoet\Validator\Builder;
use MailPoet\Validator\Schema\ObjectSchema;
use MailPoet\WP\Functions;

class AutomateWooTrigger implements Trigger
{

  private $automationStorage;
  private $subscribersRepository;
  private $wp;

  public function __construct(
    AutomationStorage $automationStorage,
    SubscribersRepository $subscribersRepository,
    Functions $wp
  ) {
    $this->automationStorage = $automationStorage;
    $this->subscribersRepository = $subscribersRepository;
    $this->wp = $wp;
  }

  public function getKey(): string {
    return 'automate-woo:trigger';
  }

  public function getName(): string {
    return __('AutomateWoo Trigger', 'mailpoet');
  }

  public function registerHooks(): void {

    add_filter(
      'automatewoo/trigger/workflows',
      [
        $this,
        'addWorkflowWrapper',
      ],
      10,
      2
    );
  }

  public function addWorkflowWrapper($workflows, $trigger) {

    $workflows[] = new WorkflowWrapper(
      $this->automationStorage,
      $this->subscribersRepository,
      $this->wp,
      $trigger,
      $this
    );
    return $workflows;
  }

  public function isTriggeredBy(StepRunArgs $args): bool {
    return true;
  }

  public function getArgsSchema(): ObjectSchema {
    return Builder::object([
      'aw_trigger' => Builder::string()->required(),
    ]);
  }

  public function getSubjectKeys(): array {
    return [
      SubscriberSubject::KEY,
    ];
  }

  public function validate(StepValidationArgs $args): void {
    return;
  }
}
