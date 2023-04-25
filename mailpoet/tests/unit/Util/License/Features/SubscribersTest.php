<?php declare(strict_types = 1);

namespace MailPoet\Test\Util\License\Features;

use Codeception\Util\Stub;
use MailPoet\Services\Bridge;
use MailPoet\Settings\SettingsController;
use MailPoet\Subscribers\SubscribersRepository;
use MailPoet\Util\License\Features\Subscribers as SubscribersFeature;
use MailPoet\WP\Functions as WPFunctions;

class SubscribersTest extends \MailPoetUnitTest {
  public function testCheckReturnsTrueIfOldUserReachedLimit() {
    $subscribersFeature = $this->constructWith([
      'mss_key_state' => 'invalid',
      'premium_key_state' => 'invalid',
      'installed_at' => '2018-11-11',
      'subscribers_count' => 2500,
      'premium_subscribers_limit' => 500,
      'mss_subscribers_limit' => 500,
    ]);
    expect($subscribersFeature->check())->true();
  }

  public function testCheckReturnsFalseIfOldUserDidntReachLimit() {
    $subscribersFeature = $this->constructWith([
      'mss_key_state' => 'invalid',
      'premium_key_state' => 'invalid',
      'installed_at' => '2018-11-11',
      'subscribers_count' => 1500,
      'premium_subscribers_limit' => 500,
      'mss_subscribers_limit' => 500,
    ]);
    expect($subscribersFeature->check())->false();
  }

  public function testCheckReturnsTrueIfNewUserReachedLimit() {
    $subscribersFeature = $this->constructWith([
      'mss_key_state' => 'invalid',
      'premium_key_state' => 'invalid',
      'installed_at' => '2019-11-11',
      'subscribers_count' => 1500,
      'premium_subscribers_limit' => 500,
      'mss_subscribers_limit' => 500,
    ]);
    expect($subscribersFeature->check())->true();
  }

  public function testCheckReturnsFalseIfNewUserDidntReachLimit() {
    $subscribersFeature = $this->constructWith([
      'mss_key_state' => 'invalid',
      'premium_key_state' => 'invalid',
      'installed_at' => '2019-11-11',
      'subscribers_count' => 900,
      'premium_subscribers_limit' => 500,
      'mss_subscribers_limit' => 500,
    ]);
    expect($subscribersFeature->check())->false();
  }

  public function testCheckReturnsFalseIfMSSKeyExistsAndDidntReachLimit() {
    $subscribersFeature = $this->constructWith([
      'mss_key_state' => 'valid',
      'premium_key_state' => 'invalid',
      'installed_at' => '2019-11-11',
      'subscribers_count' => 2500,
      'premium_subscribers_limit' => 500,
      'mss_subscribers_limit' => 3500,
    ]);
    expect($subscribersFeature->check())->false();
  }

  public function testCheckReturnsTrueIfMSSKeyExistsAndReachedLimit() {
    $subscribersFeature = $this->constructWith([
      'mss_key_state' => 'valid',
      'premium_key_state' => 'invalid',
      'installed_at' => '2019-11-11',
      'subscribers_count' => 3000,
      'premium_subscribers_limit' => 500,
      'mss_subscribers_limit' => 2500,
    ]);
    expect($subscribersFeature->check())->true();
  }

  public function testCheckReturnsTrueIfMSSKeyIsExpiringAndReachedLimit() {
    $subscribersFeature = $this->constructWith([
      'mss_key_state' => 'expiring',
      'premium_key_state' => 'invalid',
      'installed_at' => '2019-11-11',
      'subscribers_count' => 3000,
      'premium_subscribers_limit' => 500,
      'mss_subscribers_limit' => 2500,
    ]);
    expect($subscribersFeature->check())->true();
  }

  public function testCheckReturnsFalseIfMSSKeyIsAlreadyUsedAndReachedLimit() {
    $subscribersFeature = $this->constructWith([
      'mss_key_state' => 'already_used',
      'premium_key_state' => 'invalid',
      'installed_at' => '2019-11-11',
      'subscribers_count' => 800,
      'premium_subscribers_limit' => 500,
      'mss_subscribers_limit' => 500,
    ]);
    expect($subscribersFeature->check())->false();
  }

  public function testCheckReturnsFalseIfPremiumKeyExistsAndDidntReachLimit() {
    $subscribersFeature = $this->constructWith([
      'mss_key_state' => 'invalid',
      'premium_key_state' => 'valid',
      'installed_at' => '2019-11-11',
      'subscribers_count' => 2500,
      'premium_subscribers_limit' => 3500,
      'mss_subscribers_limit' => 500,
    ]);
    expect($subscribersFeature->check())->false();
  }

  public function testCheckReturnsTrueIfPremiumKeyExistsAndReachedLimit() {
    $subscribersFeature = $this->constructWith([
      'mss_key_state' => 'invalid',
      'premium_key_state' => 'valid',
      'installed_at' => '2019-11-11',
      'subscribers_count' => 3000,
      'premium_subscribers_limit' => 2500,
      'mss_subscribers_limit' => 500,
    ]);
    expect($subscribersFeature->check())->true();
  }

  public function testCheckReturnsFalseIfPremiumKeyExistsButLimitMissing() {
    $subscribersFeature = $this->constructWith([
      'mss_key_state' => 'invalid',
      'premium_key_state' => 'valid',
      'installed_at' => '2019-11-11',
      'subscribers_count' => 3000,
      'premium_subscribers_limit' => false,
      'mss_subscribers_limit' => false,
    ]);
    expect($subscribersFeature->check())->false();
  }

  public function testCheckReturnsFalseIfMSSKeyExistsButLimitMissing() {
    $subscribersFeature = $this->constructWith([
      'mss_key_state' => 'valid',
      'premium_key_state' => 'invalid',
      'installed_at' => '2019-11-11',
      'subscribers_count' => 3000,
      'premium_subscribers_limit' => false,
      'mss_subscribers_limit' => false,
    ]);
    expect($subscribersFeature->check())->false();
  }

  public function testCheckReturnsTrueIfPremiumSupportAndReachedLimit() {
    $subscribersFeature = $this->constructWith([
      'has_mss_key' => false,
      'mss_key_state' => 'valid',
      'premium_key_state' => 'valid',
      'has_premium_key' => true,
      'installed_at' => '2019-11-11',
      'subscribers_count' => 3000,
      'premium_subscribers_limit' => 2500,
      'mss_subscribers_limit' => 500,
      'support_tier' => 'premium',
    ]);
    expect($subscribersFeature->check())->true();
  }

  private function constructWith($specs) {
    $settings = Stub::make(SettingsController::class, [
      'get' => function($name) use($specs) {
        if ($name === 'installed_at') return $specs['installed_at'];
      },
    ]);

    $subscribersRepository = Stub::make(SubscribersRepository::class, [
      'getTotalSubscribers' => function() use($specs) {
        return $specs['subscribers_count'];
      },
    ]);

    $wpFunctions = Stub::make(WPFunctions::class, [
      'getTransient' => false,
      'setTransient' => false,
    ]);

    $bridge = Stub::make(Bridge::class, [
      'getPremiumKeyState' => [
        'state' => $specs['premium_key_state'],
        'data' => [
          'site_active_subscriber_limit' => $specs['premium_subscribers_limit'],
          'support_tier' => $specs['support_tier'] ?? 'free',
        ],
      ],
      'getMssKeyState' => [
        'state' => $specs['mss_key_state'],
        'data' => [
          'site_active_subscriber_limit' => $specs['mss_subscribers_limit'],
        ],
      ],
    ]);

    return new SubscribersFeature($settings, $subscribersRepository, $wpFunctions, $bridge);
  }
}
