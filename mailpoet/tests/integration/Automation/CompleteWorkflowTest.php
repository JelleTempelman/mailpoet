<?php
namespace MailPoet\Test\Automation\Engine\Storage;

use MailPoet\Automation\Engine\Data\NextStep;
use MailPoet\Automation\Engine\Data\Step;
use MailPoet\Automation\Engine\Data\Workflow;
use MailPoet\Automation\Engine\Data\WorkflowRun;
use MailPoet\Automation\Engine\Hooks;
use MailPoet\Automation\Engine\Registry;
use MailPoet\Automation\Engine\Storage\WorkflowRunStorage;
use MailPoet\Automation\Engine\Storage\WorkflowStorage;
use MailPoet\Automation\Integrations\MailPoet\Triggers\SomeoneSubscribesTrigger;
use MailPoet\Entities\SegmentEntity;
use MailPoet\Entities\SubscriberEntity;
use MailPoet\Segments\SegmentsRepository;
use MailPoet\Subscribers\SubscriberSegmentRepository;
use MailPoet\Subscribers\SubscribersRepository;
use MailPoet\Test\Automation\Integrations\Test\Actions\TestAction;

include_once __DIR__ . '/Integrations/Test/TestAction.php';

class CompleteWorkflowTest extends \MailPoetTest
{

  /** @var WorkflowStorage */
  private $workflowStorage;

  /** @var WorkflowRunStorage */
  private $workflowRunStorage;

  /** @var SubscriberSegmentRepository */
  private $subscriberSegmentsRepository;

  /** @var SubscribersRepository */
  private $subscribersRepository;

  /** @var SegmentsRepository */
  private $segmentsRepository;

  /** @var Registry */
  private $registry;


  public function _before() : void {
    $this->workflowStorage = $this->diContainer->get(WorkflowStorage::class);
    $this->workflowRunStorage = $this->diContainer->get(WorkflowRunStorage::class);
    $this->subscriberSegmentsRepository = $this->diContainer->get(SubscriberSegmentRepository::class);
    $this->segmentsRepository = $this->diContainer->get(SegmentsRepository::class);
    $this->subscribersRepository = $this->diContainer->get(SubscribersRepository::class);
    $this->registry = $this->diContainer->get(Registry::class);
    $this->registry->addAction(new TestAction());
  }

  public function _after() {
    $this->subscribersRepository->truncate();
    $this->subscribersRepository->flush();
    $this->subscriberSegmentsRepository->truncate();
    $this->subscriberSegmentsRepository->flush();
    $this->segmentsRepository->truncate();
    $this->segmentsRepository->flush();
    $this->workflowStorage->truncate();
    $this->workflowRunStorage->truncate();
  }

  /**
   * This test verifies the lifecycle of a workflow run does work.
   *
   * @return void
   */
  public function testWorkflowFromSchedulingToCompleted() {
    /**
     * We want only to run one step per run of the queue. Limiting the batch size does not work here,
     * but claiming that memory exceeded does.
     */
    add_filter('action_scheduler_memory_exceeded', function(){return true;});

    $runner = \ActionScheduler::runner();

    $this->assertEmpty($this->workflowStorage->getActiveTriggerKeys());
    $this->storeActiveWorkflow();
    $activeTriggers = $this->workflowStorage->getActiveTriggerKeys();
    $this->assertContains('mailpoet:someone-subscribes', $activeTriggers);

    /**
     * Register trigger hook
     * The engine did register the active hooks already before we stored our workflow.
     * Therefore, we need to register the hook ourselves.
     *
     * Another way would be to call do_action('init'). The problem here is currently, we do
     * not allow registered actions etc. to be overwritten and throw an error.
     *
     * See Registry::addAction()
     */
    $activeTrigger = $this->registry->getTrigger((string) current($activeTriggers));
    assert($activeTrigger instanceof SomeoneSubscribesTrigger);
    $activeTrigger->registerHooks();

    /**
     * No workflow run is queued.
     */
    $this->assertFalse(as_has_scheduled_action(Hooks::WORKFLOW_STEP), 'A workflow run is not yet scheduled');
    $this->registerNewSubscriberToTriggerWorkflow();

    /**
     * A workflow run is queued.
     */
    $this->assertTrue(as_next_scheduled_action(Hooks::WORKFLOW_STEP), 'A workflow run is scheduled');

    /** @var \ActionScheduler_Action[] $actions */
    $actions = as_get_scheduled_actions(['hook' => Hooks::WORKFLOW_STEP]);
    $currentAction = current($actions);
    $this->assertInstanceOf(\ActionScheduler_Action::class, $currentAction);
    $this->assertCount(1, $actions);
    $this->assertSame('action', $currentAction->get_args()[0]['step_id'], 'The 1. action step was expected to be scheduled');
    $this->assertNotInstanceOf(\ActionScheduler_FinishedAction::class, $currentAction);

    /**
     * Run the queue 1 time.
     */
    $runner->run();

    /** @var \ActionScheduler_Action[] $actions */
    $actions = as_get_scheduled_actions(['hook' => Hooks::WORKFLOW_STEP]);
    $currentAction = current($actions);
    $this->assertInstanceOf(\ActionScheduler_FinishedAction::class, $currentAction);
    $currentAction = next($actions);
    $this->assertInstanceOf(\ActionScheduler_Action::class, $currentAction);
    $workflowRunId = $currentAction->get_args()[0]['workflow_run_id'];
    $this->assertSame('action-2', $currentAction->get_args()[0]['step_id'], 'The action-2 step was expected to be scheduled');
    $this->assertNotInstanceOf(\ActionScheduler_FinishedAction::class, $currentAction);
    $workflowRun = $this->workflowRunStorage->getWorkflowRun($workflowRunId);
    $this->assertInstanceOf(WorkflowRun::class, $workflowRun);
    $this->assertSame(WorkflowRun::STATUS_RUNNING, $workflowRun->getStatus());

    /**
     * Run the queue again
     */
    $runner->run();

    $actions = as_get_scheduled_actions(['hook' => Hooks::WORKFLOW_STEP]);
    $this->assertCount(2, $actions);
    $currentAction = end($actions);
    $this->assertInstanceOf(\ActionScheduler_Action::class, $currentAction);
    $this->assertSame('action-2', $currentAction->get_args()[0]['step_id'], 'The action-2 step was expected to be done.');


    $workflowRun = $this->workflowRunStorage->getWorkflowRun($workflowRunId);
    $this->assertInstanceOf(WorkflowRun::class, $workflowRun);
    $this->assertSame(WorkflowRun::STATUS_COMPLETE, $workflowRun->getStatus());
  }


  private function registerNewSubscriberToTriggerWorkflow() {
    $segment = new SegmentEntity('list', SegmentEntity::TYPE_DEFAULT, 'description');
    $this->segmentsRepository->persist($segment);
    $this->segmentsRepository->flush();
    $segment = $this->segmentsRepository->findOneBy(['name' => 'list']);
    assert($segment instanceof SegmentEntity);
    $subscriber = new SubscriberEntity();
    $subscriber->setEmail('workflowrun-test@mailpoet.com');
    $subscriber->setStatus(SubscriberEntity::STATUS_SUBSCRIBED);
    $this->subscribersRepository->persist($subscriber);
    $this->subscribersRepository->flush();

    $this->subscriberSegmentsRepository->subscribeToSegments($subscriber, [$segment]);
    $this->subscriberSegmentsRepository->flush();
  }

  private function storeActiveWorkflow() {
    $action2 = new Step('action-2', Step::TYPE_ACTION, 'test:empty', [], []);
    $action = new Step('action', Step::TYPE_ACTION, 'test:empty', [], [new NextStep($action2->getId())]);
    $trigger = new Step('trigger', Step::TYPE_TRIGGER, 'mailpoet:someone-subscribes', [], [new NextStep($action->getId())]);

    /**
     * @ToDo: Replace type parameter with Step::TYPE_ROOT, once it lands trunk.
     */
    $root = new Step('root', 'root', 'core:root', [], [new NextStep($trigger->getId())]);
    $steps = [$root, $trigger, $action, $action2]    ;
    $workflow = new Workflow('name', $steps, new \WP_User(1));
    $workflow->setStatus(Workflow::STATUS_ACTIVE);
    $this->workflowStorage->createWorkflow($workflow);
  }
}
