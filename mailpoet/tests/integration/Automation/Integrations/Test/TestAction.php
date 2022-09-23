<?php

namespace MailPoet\Test\Automation\Integrations\Test\Actions;


use _HumbugBox86e303171e7c\Symfony\Component\Console\Exception\LogicException;
use MailPoet\Automation\Engine\Data\Step as StepData;
use MailPoet\Automation\Engine\Data\StepRunArgs;
use MailPoet\Automation\Engine\Data\Workflow;
use MailPoet\Automation\Engine\Data\WorkflowRun;
use MailPoet\Automation\Engine\Workflows\Action;
use MailPoet\Validator\Builder;
use MailPoet\Validator\Schema\ObjectSchema;

class TestAction implements Action
{

  public function isValid(array $subjects, StepData $step, Workflow $workflow): bool {
    return true;
  }

  public function run(Workflow $workflow, WorkflowRun $workflowRun, StepData $step): void {
    return;
  }

  public function getKey(): string {
    return 'test:empty';
  }

  public function getName(): string {
    return 'Empty';
  }

  public function getArgsSchema(): ObjectSchema {
    return Builder::object();
  }

  public function getSubjectKeys(): array {
    return [
      'mailpoet:segment',
      'mailpoet:subscriber',
    ];
  }
}
