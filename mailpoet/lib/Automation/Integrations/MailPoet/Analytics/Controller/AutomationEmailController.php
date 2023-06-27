<?php declare(strict_types = 1);

namespace MailPoet\Automation\Integrations\MailPoet\Analytics\Controller;

use MailPoet\Automation\Engine\Data\Automation;
use MailPoet\Automation\Engine\Storage\AutomationStorage;
use MailPoet\Automation\Integrations\MailPoet\Actions\SendEmailAction;
use MailPoet\Entities\NewsletterEntity;
use MailPoet\Newsletter\NewslettersRepository;

class AutomationEmailController {


  /** @var AutomationStorage */
  private $automationStorage;

  /** @var NewslettersRepository */
  private $newslettersRepository;

  public function __construct(
    AutomationStorage $automationStorage,
    NewslettersRepository $newslettersRepository
  ) {
    $this->automationStorage = $automationStorage;
    $this->newslettersRepository = $newslettersRepository;
  }

  /**
   * @param Automation $automation
   * @param \DateTimeImmutable $after
   * @param \DateTimeImmutable $before
   * @return NewsletterEntity[]
   */
  public function getAutomationEmailsInTimeSpan(Automation $automation, \DateTimeImmutable $after, \DateTimeImmutable $before): array {
    $automationVersions = $this->automationStorage->getAutomationVersionDates($automation->getId());
    usort(
      $automationVersions,
      function (array $a, array $b) {
        return $a['created_at'] <=> $b['created_at'];
      }
    );

    // filter automations that were created before the after date
    $versionIds = [];
    foreach ($automationVersions as $automationVersion) {
      if ($automationVersion['created_at'] > $before) {
        break;
      }
      if (!$versionIds || $automationVersion['created_at'] < $after) {
        $versionIds = [(int)$automationVersion['id']];
        continue;
      }
      $versionIds[] = (int)$automationVersion['id'];
    }

    $automations = $this->automationStorage->getAutomationWithDifferentVersions($versionIds);
    return $this->getEmailsFromAutomations($automations);
  }

  /**
   * @param Automation[] $automations
   * @return NewsletterEntity[]
   */
  public function getEmailsFromAutomations(array $automations): array {
    $emailSteps = [];
    foreach ($automations as $automation) {
      $emailSteps = array_merge(
        $emailSteps,
        array_values(
          array_filter(
            $automation->getSteps(),
            function($step) {
              return $step->getKey() === SendEmailAction::KEY;
            }
          )
        )
      );
    }
    $emailIds = array_unique(
      array_filter(
        array_map(
          function($step) {
            return $step->getArgs()['email_id'];
          },
          $emailSteps
        )
      )
    );

    return $this->newslettersRepository->findBy(['id' => $emailIds]);
  }
}
