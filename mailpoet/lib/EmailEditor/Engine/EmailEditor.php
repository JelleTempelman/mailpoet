<?php declare(strict_types = 1);

namespace MailPoet\EmailEditor\Engine;

use MailPoet\EmailEditor\Engine\Patterns\Patterns;
use MailPoet\EmailEditor\Engine\Templates\Templates;
use MailPoet\Entities\NewsletterEntity;
use WP_Post;
use WP_Theme_JSON;

/**
 * @phpstan-type EmailPostType array{name: string, args: array, meta: array{key: string, args: array}[]}
 * See register_post_type for details about EmailPostType args.
 */
class EmailEditor {
  public const MAILPOET_EMAIL_META_THEME_TYPE = 'mailpoet_email_theme';

  private EmailApiController $emailApiController;
  private Templates $templates;
  private Patterns $patterns;

  public function __construct(
    EmailApiController $emailApiController,
    Templates $templates,
    Patterns $patterns
  ) {
    $this->emailApiController = $emailApiController;
    $this->templates = $templates;
    $this->patterns = $patterns;
  }

  public function initialize(): void {
    do_action('mailpoet_email_editor_initialized');
    add_filter('mailpoet_email_editor_rendering_theme_styles', [$this, 'extendEmailThemeStyles'], 10, 2);
    $this->registerBlockTemplates();
    $this->registerBlockPatterns();
    $this->registerEmailPostTypes();
    $this->registerEmailPostSendStatus();
    $this->extendEmailPostApi();
  }

  private function registerBlockTemplates(): void {
    $this->templates->initialize();
  }

  private function registerBlockPatterns(): void {
    $this->patterns->initialize();
  }

  /**
   * Register all custom post types that should be edited via the email editor
   * The post types are added via mailpoet_email_editor_post_types filter.
   */
  private function registerEmailPostTypes(): void {
    foreach ($this->getPostTypes() as $postType) {
      register_post_type(
        $postType['name'],
        array_merge($this->getDefaultEmailPostArgs(), $postType['args'])
      );
    }
  }

  /**
   * @phpstan-return EmailPostType[]
   */
  private function getPostTypes(): array {
    $postTypes = [];
    return apply_filters('mailpoet_email_editor_post_types', $postTypes);
  }

  private function getDefaultEmailPostArgs(): array {
    return [
      'public' => false,
      'hierarchical' => false,
      'show_ui' => true,
      'show_in_menu' => false,
      'show_in_nav_menus' => false,
      'supports' => ['editor', 'title', 'custom-fields'], // 'custom-fields' is required for loading meta fields via API
      'has_archive' => true,
      'show_in_rest' => true, // Important to enable Gutenberg editor
    ];
  }

  private function registerEmailPostSendStatus(): void {
    register_post_status(NewsletterEntity::STATUS_SENT, [
        'public' => false,
        'exclude_from_search' => true,
        'internal' => true, // for now, we hide it, if we use the status in the listings we may flip this and following values
        'show_in_admin_all_list' => false,
        'show_in_admin_status_list' => false,
      ]);
  }

  public function extendEmailPostApi() {
    $emailPostTypes = array_column($this->getPostTypes(), 'name');
    register_rest_field($emailPostTypes, 'email_data', [
      'get_callback' => [$this->emailApiController, 'getEmailData'],
      'update_callback' => [$this->emailApiController, 'saveEmailData'],
      'schema' => $this->emailApiController->getEmailDataSchema(),
    ]);
  }

  public function getEmailThemeDataSchema(): array {
    return (new EmailStylesSchema())->getSchema();
  }

  public function extendEmailThemeStyles(WP_Theme_JSON $theme, WP_Post $post): WP_Theme_JSON {
    $emailTheme = get_post_meta($post->ID, EmailEditor::MAILPOET_EMAIL_META_THEME_TYPE, true);
    if ($emailTheme && is_array($emailTheme)) {
      $theme->merge(new WP_Theme_JSON($emailTheme));
    }
    return $theme;
  }
}
