<?php declare(strict_types = 1);

namespace MailPoet\WooCommerce\MultichannelMarketing;

use Automattic\WooCommerce\Admin\Marketing\MarketingCampaign;
use Automattic\WooCommerce\Admin\Marketing\MarketingCampaignType;
use Automattic\WooCommerce\Admin\Marketing\MarketingChannelInterface;
use Automattic\WooCommerce\Admin\Marketing\Price;
use MailPoet\Config\Menu;
use MailPoet\DI\ContainerWrapper;
use MailPoet\Entities\NewsletterEntity;
use MailPoet\Newsletter\NewslettersRepository;
use MailPoet\Settings\SettingsController;

class MPMarketingChannel implements MarketingChannelInterface {
  /**
   * @var MarketingCampaignType[]
   */
  protected $campaignTypes;

  /**
   * @var SettingsController
   */
  protected $settings;

  /**
   * @var NewslettersRepository
   */
  protected $newsletterRepository;

  public function __construct() {
    $this->settings = ContainerWrapper::getInstance()->get(SettingsController::class);
    $this->newsletterRepository = ContainerWrapper::getInstance()->get(NewslettersRepository::class);
    $this->campaignTypes = $this->generateCampaignTypes();
  }

  /**
   * Returns the unique identifier string for the marketing channel extension, also known as the plugin slug.
   *
   * @return string
   */
  public function get_slug(): string { // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    return 'mailpoet';
  }

  /**
   * Returns the name of the marketing channel.
   *
   * @return string
   */
  public function get_name(): string { // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    return __( 'MailPoet', 'mailpoet' );
  }

  /**
   * Returns the description of the marketing channel.
   *
   * @return string
   */
  public function get_description(): string { // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    return __( 'Create and send newsletters, post notifications and welcome emails from your WordPress.', 'mailpoet' );
  }

  /**
   * Returns the path to the channel icon.
   *
   * @return string
   */
  public function get_icon_url(): string { // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    // TODO: use the correct image
    return 'https://ps.w.org/mailpoet/assets/icon-256x256.png';
  }

  /**
   * Returns the setup status of the marketing channel.
   *
   * @return bool
   */
  public function is_setup_completed(): bool { // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
     return $this->isMPSetupComplete();
  }

  /**
   * Returns the URL to the settings page, or the link to complete the setup/onboarding if the channel has not been set up yet.
   *
   * @return string
   */
  public function get_setup_url(): string { // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    if ($this->is_setup_completed()) {
      return admin_url('admin.php?page=' . Menu::MAIN_PAGE_SLUG);
    }

    return admin_url('admin.php?page=' . Menu::WELCOME_WIZARD_PAGE_SLUG . '&mailpoet_wizard_loaded_via_woocommerce');
  }

  /**
   * Returns the status of the marketing channel's product listings.
   *
   * @return string
   */
  public function get_product_listings_status(): string { // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    // TODO: find out if we can use a different text here
    return self::PRODUCT_LISTINGS_SYNCED;
  }

  /**
   * Returns the number of channel issues/errors (e.g. account-related errors, product synchronization issues, etc.).
   *
   * @return int The number of issues to resolve, or 0 if there are no issues with the channel.
   */
  public function get_errors_count(): int { // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    // TODO: fetch correct value
    return 0;
  }

  /**
   * Returns an array of marketing campaign types that the channel supports.
   *
   * @return MarketingCampaignType[] Array of marketing campaign type objects.
   */
  public function get_supported_campaign_types(): array { // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    return $this->campaignTypes;
  }

  /**
   * Returns an array of the channel's marketing campaigns.
   *
   * @return MarketingCampaign[]
   */
  public function get_campaigns(): array { // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    // TODO: fetch the correct value
    $allCampaigns = $this->generateCampaigns();

    if (empty($allCampaigns)) {
      return [];
    }

    return $allCampaigns;
  }

  /**
   * Whether the task is completed.
   * If the setting 'version' is not null it means the welcome wizard
   * was already completed so we mark this task as completed as well.
   */
  protected function isMPSetupComplete(): bool {
    $version = $this->settings->get('version');

    return $version !== null;
  }

  protected function generateCampaignTypes(): array {
    return [
      'mailpoet-newsletters' => new MarketingCampaignType(
        'mailpoet-newsletters',
        $this,
        'MailPoet Newsletters',
        'Send a newsletter with images, buttons, dividers, and social bookmarks. Or, just send a basic text email.',
        admin_url('admin.php?page=' . Menu::EMAILS_PAGE_SLUG . '#/new'),
        $this->get_icon_url()
      ),
      'mailpoet-post-notifications' => new MarketingCampaignType(
        'mailpoet-post-notifications',
        $this,
        'MailPoet Post notifications',
        'Email your subscribers your latest content. You can send daily, weekly, monthly, or even immediately after publication.',
        admin_url('admin.php?page=' . Menu::EMAILS_PAGE_SLUG . '#/new/notification'),
        $this->get_icon_url()
      ),
      'mailpoet-automations' => new MarketingCampaignType(
        'mailpoet-automations',
        $this,
        'MailPoet Automations',
        'Set up automations to send abandoned cart reminders, welcome new subscribers, celebrate first-time buyers, and much more.',
        admin_url('admin.php?page=' . Menu::AUTOMATION_TEMPLATES_PAGE_SLUG),
        $this->get_icon_url()
      ),
    ];
  }

  protected function getStandardNewsletterList(): array {
    $result = [];
    foreach ($this->newsletterRepository->getStandardNewsletterList() as $newsletter) {
      $newsLetterId = (string)$newsletter->getId();
      $result[] = [
        'id' => $newsLetterId,
        'name' => $newsletter->getSubject(),
        'campaignType' => $this->campaignTypes['mailpoet-newsletters'],
        'url' => admin_url('admin.php?page=' . Menu::EMAILS_PAGE_SLUG . '/#/stats/' . $newsLetterId), // TODO: Woo issue, not working.
        'price' => [
          // TODO: fetch the correct value
          'amount' => 0,
          'currency' => 'USD',
        ],
      ];
    }
    return $result;
  }

  protected function getPostNotificationNewsletters(): array {
    $newsletters = $this->newsletterRepository->findActiveByTypes([NewsletterEntity::TYPE_NOTIFICATION]);

    $result = [];

    foreach ($newsletters as $newsletter) {
      $newsLetterId = (string)$newsletter->getId();
      $result[] = [
        'id' => $newsLetterId,
        'name' => $newsletter->getSubject(),
        'campaignType' => $this->campaignTypes['mailpoet-post-notifications'],
        'url' => admin_url('admin.php?page=' . Menu::EMAILS_PAGE_SLUG . '/#/stats/' . $newsLetterId), // TODO: Woo issue, not working.
        'price' => [
          // TODO: fetch the correct value
          'amount' => 0,
          'currency' => 'USD',
        ],
      ];
    }
    return $result;
  }

  protected function getAutomationNewsletters(): array {
    // TODO: Implement me
    return [];
  }

  protected function generateCampaigns(): array {
    return array_map(
      function (array $data) {
        $cost = null;

        if (isset( $data['price'] )) {
          $cost = new Price( (string)$data['price']['amount'], $data['price']['currency'] );
        }

        return new MarketingCampaign(
          $data['id'],
          $data['campaignType'],
          $data['name'],
          $data['url'],
          $cost,
        );
      },
      array_merge(
        $this->getStandardNewsletterList(),
        $this->getPostNotificationNewsletters(),
        $this->getAutomationNewsletters()
      )
    );
  }
}
