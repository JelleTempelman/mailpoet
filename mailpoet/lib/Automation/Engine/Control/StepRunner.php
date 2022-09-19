<?php declare(strict_types = 1);

namespace MailPoet\Automation\Engine\Control;

use MailPoet\Automation\Engine\Data\Step;
use MailPoet\Automation\Engine\Data\SubjectEntry;
use MailPoet\Automation\Engine\Data\Workflow;
use MailPoet\Automation\Engine\Data\WorkflowRun;

interface StepRunner {
  /** @var SubjectEntry[] $subjectEntries */
  public function run(Step $step, Workflow $workflow, WorkflowRun $workflowRun, array $subjectEntries): void;
}
