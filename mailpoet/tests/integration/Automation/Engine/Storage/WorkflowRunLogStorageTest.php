<?php

namespace MailPoet\Test\Automation\Engine\Storage;

use MailPoet\Automation\Engine\Data\WorkflowRunLog;
use MailPoet\Automation\Engine\Storage\WorkflowRunLogStorage;

class WorkflowRunLogStorageTest extends \MailPoetTest {

  /** @var WorkflowRunLogStorage */
  private $storage;

  public function _before() {
    $this->storage = $this->diContainer->get(WorkflowRunLogStorage::class);
  }

  public function testItSavesAndRetrievesAsExpected() {
    $log = new WorkflowRunLog(1, 'step-id', []);
    $log->setData('key', 'value');
    $log->setData('key2', ['arrayData']);
    $preSave = $log->toArray();
    $id = $this->storage->createWorkflowRunLog($log);
    $fromDatabase = $this->storage->getWorkflowRunLog($id);
    $this->assertInstanceOf(WorkflowRunLog::class, $fromDatabase);
    expect($preSave)->equals($fromDatabase->toArray());
  }

  public function testItStoresErrors() {
    $log = new WorkflowRunLog(1, 'step-id', []);
    $log->addError(new \Exception('test'));
    $id = $this->storage->createWorkflowRunLog($log);
    $log = $this->storage->getWorkflowRunLog($id);
    $this->assertInstanceOf(WorkflowRunLog::class, $log);
    $errors = $log->getErrors();
    expect($errors)->count(1);
  }

  public function _after() {
    global $wpdb;
    $sql = 'truncate ' . $wpdb->prefix . 'mailpoet_workflow_run_logs';
    $wpdb->query($sql);
  }
}
