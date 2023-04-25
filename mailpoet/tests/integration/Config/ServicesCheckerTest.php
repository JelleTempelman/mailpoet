<?php declare(strict_types = 1);

namespace MailPoet\Test\Config;

use MailPoet\Config\ServicesChecker;
use MailPoet\Mailer\Mailer;
use MailPoet\Services\Bridge;
use MailPoet\Settings\SettingsController;
use MailPoet\Test\DataFactories\Settings;

class ServicesCheckerTest extends \MailPoetTest {

  /** @var ServicesChecker */
  private $servicesChecker;

  /** @var SettingsController */
  private $settings;

  /** @var Settings */
  private $settingsFactory;

  public function _before() {
    parent::_before();
    $this->settings = SettingsController::getInstance();
    $this->servicesChecker = new ServicesChecker();
    $this->settingsFactory = new Settings();
    $this->setMailPoetSendingMethod();
  }

  public function testItDoesNotCheckMSSKeyIfMPSendingServiceIsDisabled() {
    $this->disableMailPoetSendingMethod();
    $result = $this->servicesChecker->isMailPoetAPIKeyValid();
    expect($result)->null();
  }

  public function testItForciblyChecksMSSKeyIfMPSendingServiceIsDisabled() {
    $this->disableMailPoetSendingMethod();
    $result = $this->servicesChecker->isMailPoetAPIKeyValid(false, true);
    expect($result)->false();
  }

  public function testItReturnsFalseIfMSSKeyIsNotSpecified() {
    $this->settingsFactory->withMssKeyAndState('', null);
    $result = $this->servicesChecker->isMailPoetAPIKeyValid();
    expect($result)->false();
  }

  public function testItReturnsTrueIfMSSKeyIsValid() {
    $this->settingsFactory->withMssKeyAndState('key', ['state' => Bridge::KEY_VALID]);
    $result = $this->servicesChecker->isMailPoetAPIKeyValid();
    expect($result)->true();
  }

  public function testItReturnsFalseIfMSSKeyIsInvalid() {
    $this->settingsFactory->withMssKeyAndState('key', ['state' => Bridge::KEY_INVALID]);
    $result = $this->servicesChecker->isMailPoetAPIKeyValid();
    expect($result)->false();
  }

  public function testItReturnsTrueIfMSSKeyIsExpiring() {
    $this->settingsFactory->withMssKeyAndState('key', [
      'state' => Bridge::KEY_EXPIRING,
      'data' => ['expire_at' => date('c')],
    ]);
    $result = $this->servicesChecker->isMailPoetAPIKeyValid();
    expect($result)->true();
  }

  public function testItReturnsFalseIfMSSKeyStateIsUnexpected() {
    $this->settingsFactory->withMssKeyAndState('key', ['state' => 'unexpected']);
    $result = $this->servicesChecker->isMailPoetAPIKeyValid();
    expect($result)->false();
  }

  public function testItReturnsFalseIfMSSKeyStateIsEmpty() {
    $this->settingsFactory->withMssKeyAndState('key', ['state' => '']);
    $result = $this->servicesChecker->isMailPoetAPIKeyValid();
    expect($result)->false();
  }

  public function testItReturnsFalseIfPremiumKeyIsNotSpecified() {
    $this->settingsFactory->withPremiumKeyAndState('', null);
    $result = $this->servicesChecker->isPremiumKeyValid();
    expect($result)->false();
  }

  public function testItReturnsTrueIfPremiumKeyIsValid() {
    $this->settingsFactory->withPremiumKeyAndState('key', ['state' => Bridge::KEY_VALID]);
    $result = $this->servicesChecker->isPremiumKeyValid();
    expect($result)->true();
  }

  public function testItReturnsFalseIfPremiumKeyIsInvalid() {
    $this->settingsFactory->withPremiumKeyAndState('key', ['state' => Bridge::KEY_INVALID]);
    $result = $this->servicesChecker->isPremiumKeyValid();
    expect($result)->false();
  }

  public function testItReturnsFalseIfPremiumKeyIsAlreadyUsed() {
    $this->settingsFactory->withPremiumKeyAndState('key', ['state' => Bridge::KEY_ALREADY_USED]);
    $result = $this->servicesChecker->isPremiumKeyValid();
    expect($result)->false();
  }

  public function testItReturnsTrueIfPremiumKeyIsExpiring() {
    $this->settingsFactory->withPremiumKeyAndState('key', [
      'state' => Bridge::KEY_EXPIRING,
      'data' => ['expire_at' => date('c')],
    ]);
    $result = $this->servicesChecker->isPremiumKeyValid();
    expect($result)->true();
  }

  public function testItReturnsFalseIfPremiumKeyStateIsUnexpected() {
    $this->settingsFactory->withPremiumKeyAndState('key', ['state' => 'unexpected']);
    $result = $this->servicesChecker->isPremiumKeyValid();
    expect($result)->false();
  }

  public function testItReturnsFalseIfPremiumKeyStateIsEmpty() {
    $this->settingsFactory->withPremiumKeyAndState('key', ['state' => '']);
    $result = $this->servicesChecker->isPremiumKeyValid();
    expect($result)->false();
  }

  public function testItReturnsAnyValidKey() {
    $premiumKey = 'premium_key';
    $mssKey = 'mss_key';
    // Only MSS is Valid
    $this->settingsFactory->withPremiumKeyAndState($premiumKey, ['state' => Bridge::KEY_INVALID]);
    $this->settingsFactory->withMssKeyAndState($mssKey, ['state' => Bridge::KEY_VALID]);
    expect($this->servicesChecker->getValidAccountKey())->equals($mssKey);

    // Only Premium is Valid
    $this->settingsFactory->withPremiumKeyAndState($premiumKey, ['state' => Bridge::KEY_VALID]);
    $this->settingsFactory->withMssKeyAndState($mssKey, ['state' => Bridge::KEY_INVALID]);
    expect($this->servicesChecker->getValidAccountKey())->equals($premiumKey);

    // Both Valid (lets use MSS in that case)
    $this->settingsFactory->withMssKeyAndState($mssKey, ['state' => Bridge::KEY_VALID]);
    expect($this->servicesChecker->getValidAccountKey())->equals($mssKey);

    // MSS is valid but underprivileged premium invalid
    $this->settingsFactory->withMssKeyAndState($mssKey, ['state' => Bridge::KEY_VALID_UNDERPRIVILEGED]);
    $this->settingsFactory->withPremiumKeyAndState($premiumKey, ['state' => Bridge::KEY_INVALID]);
    expect($this->servicesChecker->getValidAccountKey())->equals($mssKey);

    // MSS is invalid, premium valid but underprivileged
    $this->settingsFactory->withMssKeyAndState($mssKey, ['state' => Bridge::KEY_INVALID]);
    $this->settingsFactory->withPremiumKeyAndState($premiumKey, ['state' => Bridge::KEY_VALID_UNDERPRIVILEGED]);
    expect($this->servicesChecker->getValidAccountKey())->equals($premiumKey);

    // None valid
    // Only MSS is Valid
    $this->settingsFactory->withMssKeyAndState($mssKey, ['state' => Bridge::KEY_INVALID]);
    $this->settingsFactory->withPremiumKeyAndState($mssKey, ['state' => Bridge::KEY_INVALID]);
    expect($this->servicesChecker->getValidAccountKey())->null();
  }

  public function testItReturnsTrueIfUserIsActivelyPaying() {
    $this->settingsFactory->withPremiumKeyAndState('key', [
      'state' => Bridge::KEY_VALID,
      'data' => ['support_tier' => 'premium'],
    ]);
    $result = $this->servicesChecker->isUserActivelyPaying();
    expect($result)->true();
  }

  public function testItReturnsFalseIfUserIsNotActivelyPaying() {
    $this->settingsFactory->withPremiumKeyAndState('key', [
      'state' => Bridge::KEY_VALID,
      'data' => ['support_tier' => 'free'],
    ]);
    $result = $this->servicesChecker->isUserActivelyPaying();
    expect($result)->false();
  }

  public function testItReturnsFalseIfUserIsNotActivelyPayingButUsingMss() {
    $this->settingsFactory->withMssKeyAndState('key', [
      'state' => Bridge::KEY_VALID,
      'data' => ['support_tier' => 'free'],
    ]);
    $result = $this->servicesChecker->isUserActivelyPaying();
    expect($result)->false();
  }

  public function testItReturnsTrueIfUserIsActivelyPayingAndUsingMss() {
    $this->settingsFactory->withMssKeyAndState('key', [
      'state' => Bridge::KEY_VALID,
      'data' => ['support_tier' => 'premium'],
    ]);
    $result = $this->servicesChecker->isUserActivelyPaying();
    expect($result)->true();
  }

  private function setMailPoetSendingMethod() {
    $this->settings->set(
      Mailer::MAILER_CONFIG_SETTING_NAME,
      [
        'method' => 'MailPoet',
        'mailpoet_api_key' => 'some_key',
      ]
    );
  }

  private function disableMailPoetSendingMethod() {
    $this->settings->set(
      Mailer::MAILER_CONFIG_SETTING_NAME,
      [
        'method' => 'PHPMail',
      ]
    );
  }
}
