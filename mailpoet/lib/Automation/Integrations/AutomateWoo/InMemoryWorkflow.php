<?php declare(strict_types = 1);

namespace MailPoet\Automation\Integrations\AutomateWoo;

use AutomateWoo\Workflow;
use WP_Post;

class InMemoryWorkflow extends Workflow {
  private $meta = [];

  public function __construct() {
    parent::__construct(new WP_Post((object)[]));
  }

  public function get_meta(string $key) {
    return $this->meta[$key] ?? null;
  }

  public function update_meta(string $key, $value): void {
    $this->meta[$key] = $value;
  }
}
