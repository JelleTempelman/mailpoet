<?php // phpcs:ignore SlevomatCodingStandard.TypeHints.DeclareStrictTypes.DeclareStrictTypesMissing

namespace MailPoet\Util\License\Features;

use MailPoet\Services\Bridge;
use MailPoet\Settings\SettingsController;
use MailPoet\Subscribers\SubscribersRepository;
use MailPoet\WP\Functions as WPFunctions;

class Subscribers {
  const SUBSCRIBERS_OLD_LIMIT = 2000;
  const SUBSCRIBERS_NEW_LIMIT = 1000;
  const NEW_LIMIT_DATE = '2019-11-00';
  const SUBSCRIBERS_COUNT_CACHE_KEY = 'mailpoet_subscribers_count';
  const SUBSCRIBERS_COUNT_CACHE_EXPIRATION_MINUTES = 60;
  const SUBSCRIBERS_COUNT_CACHE_MIN_VALUE = 1000;

  /** @var SettingsController */
  private $settings;

  /** @var SubscribersRepository */
  private $subscribersRepository;

  /** @var WPFunctions */
  private $wp;

  /** @var Bridge */
  private $bridge;

  public function __construct(
    SettingsController $settings,
    SubscribersRepository $subscribersRepository,
    WPFunctions $wp,
    Bridge $bridge
  ) {
    $this->settings = $settings;
    $this->subscribersRepository = $subscribersRepository;
    $this->wp = $wp;
    $this->bridge = $bridge;
  }

  public function check(): bool {
    $limit = $this->getSubscribersLimit();
    if ($limit === false) return false;
    $subscribersCount = $this->getSubscribersCount();
    return $subscribersCount > $limit;
  }

  public function checkEmailVolumeLimitIsReached(): bool {
    $emailVolumeLimit = $this->getEmailVolumeLimit();
    if (!$emailVolumeLimit) {
      return false;
    }
    $emailsSent = $this->getEmailsSent();
    return $emailsSent > $emailVolumeLimit;
  }

  public function getSubscribersCount(): int {
    $count = $this->wp->getTransient(self::SUBSCRIBERS_COUNT_CACHE_KEY);
    if (is_numeric($count)) {
      return (int)$count;
    }
    $count = $this->subscribersRepository->getTotalSubscribers();

    // cache only when number of subscribers exceeds minimum value
    if ($count > self::SUBSCRIBERS_COUNT_CACHE_MIN_VALUE) {
      $this->wp->setTransient(self::SUBSCRIBERS_COUNT_CACHE_KEY, $count, self::SUBSCRIBERS_COUNT_CACHE_EXPIRATION_MINUTES * 60);
    }
    return $count;
  }

  public function hasValidApiKey(): bool {
    return $this->hasValidMssKey() || $this->hasValidPremiumKey();
  }

  public function getSubscribersLimit() {
    if (!$this->hasValidApiKey()) {
      return $this->getFreeSubscribersLimit();
    }

    if ($this->hasValidMssKey() && $this->hasMssSubscribersLimit()) {
      return $this->getMssSubscribersLimit();
    }

    if ($this->hasValidPremiumKey() && $this->hasPremiumSubscribersLimit()) {
      return $this->getPremiumSubscribersLimit();
    }

    return false;
  }

  public function getEmailVolumeLimit(): int {
    $stateData = $this->bridge->getPremiumKeyState();
    return (int)($stateData['data']['email_volume_limit'] ?? null);
  }

  public function getEmailsSent(): int {
    $stateData = $this->bridge->getPremiumKeyState();
    return (int)($stateData['data']['emails_sent'] ?? null);
  }

  public function hasValidMssKey() {
    $stateData = $this->bridge->getMssKeyState();
    $state = $stateData['state'] ?? null;
    return $state === Bridge::KEY_VALID || $state === Bridge::KEY_EXPIRING;
  }

  private function hasMssSubscribersLimit() {
    return !empty($this->getMssSubscribersLimit());
  }

  private function getMssSubscribersLimit() {
    $stateData = $this->bridge->getMssKeyState() ?? [];
    return (int)($stateData['data']['site_active_subscriber_limit'] ?? 0);
  }

  public function hasMssPremiumSupport() {
    if (!$this->hasValidMssKey()) {
      return false;
    }
    $stateData = $this->bridge->getMssKeyState() ?? [];
    $supportTier = $stateData['data']['support_tier'] ?? null;
    return $supportTier === 'premium';
  }

  public function hasValidPremiumKey() {
    $stateData = $this->bridge->getPremiumKeyState();
    $state = $stateData['state'] ?? null;
    return $state === Bridge::KEY_VALID || $state === Bridge::KEY_EXPIRING;
  }

  private function hasPremiumSubscribersLimit() {
    return !empty($this->getPremiumSubscribersLimit());
  }

  private function getPremiumSubscribersLimit() {
    $stateData = $this->bridge->getPremiumKeyState() ?? [];
    return (int)($stateData['data']['site_active_subscriber_limit'] ?? 0);
  }

  public function hasPremiumSupport() {
    if (!$this->hasValidPremiumKey()) {
      return false;
    }
    $stateData = $this->bridge->getPremiumKeyState() ?? [];
    $supportTier = $stateData['data']['support_tier'] ?? null;
    return $supportTier === 'premium';
  }

  private function getFreeSubscribersLimit() {
    $installationTime = strtotime((string)$this->settings->get('installed_at'));
    $oldUser = $installationTime < strtotime(self::NEW_LIMIT_DATE);
    return $oldUser ? self::SUBSCRIBERS_OLD_LIMIT : self::SUBSCRIBERS_NEW_LIMIT;
  }
}
