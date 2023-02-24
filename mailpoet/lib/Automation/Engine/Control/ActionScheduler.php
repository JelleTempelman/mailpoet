<?php declare(strict_types = 1);

namespace MailPoet\Automation\Engine\Control;

class ActionScheduler {
  private const GROUP_ID = 'mailpoet-automation';

  public function enqueue(string $hook, array $args = []): int {
    return as_enqueue_async_action($hook, $args, self::GROUP_ID);
  }

  public function schedule(int $timestamp, string $hook, array $args = []): int {
    return as_schedule_single_action($timestamp, $hook, $args, self::GROUP_ID);
  }

  public function scheduleRecurring(int $timestamp, int $intervalInSeconds, string $hook, array $args = [], bool $unique = true): int {
    return as_schedule_recurring_action($timestamp, $intervalInSeconds, $hook, $args, self::GROUP_ID, $unique);
  }

  public function hasScheduledAction(string $hook, array $args = []): bool {
    return as_has_scheduled_action($hook, $args, self::GROUP_ID);
  }

  public function unscheduleAction(string $hook, array $args = []): ?int {
    $id = as_unschedule_action($hook, $args, self::GROUP_ID);
    return $id === null ? null : intval($id);
  }
}
