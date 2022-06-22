<?php

namespace MailPoet\AdminPages\Pages;

use MailPoet\AdminPages\PageRenderer;
use MailPoet\Config\Env;
use MailPoet\Newsletter\GutenbergFormatMapper;
use MailPoet\Newsletter\NewslettersRepository;
use MailPoet\WP\Functions as WPFunctions;

class NewsletterEditorV2 {
  /** @var PageRenderer */
  private $pageRenderer;

  /** @var WPFunctions */
  private $wp;

  /** @var NewslettersRepository */
  private $newsletterRepository;

  /** @var GutenbergFormatMapper */
  private $gutenbergMapper;

  public function __construct(
    PageRenderer $pageRenderer,
    WPFunctions $wp,
    NewslettersRepository $newsletterRepository,
    GutenbergFormatMapper $gutenbergFormatMapper
  ) {
    $this->pageRenderer = $pageRenderer;
    $this->wp = $wp;
    $this->newsletterRepository = $newsletterRepository;
    $this->gutenbergMapper = $gutenbergFormatMapper;
  }

  public function render() {
    $newsletterId = (isset($_GET['id']) ? (int)$_GET['id'] : 0);
    $newsletter = $this->newsletterRepository->findOneById($newsletterId);
    $newsletterBody = '';
    if ($newsletter) {
      $newsletterBody = $this->gutenbergMapper->map($newsletter->getBody() ?? []);
    }
    // Gutenberg styles
    $this->wp->wpEnqueueStyle('mailpoet_email_editor_v2', Env::$assetsUrl . '/dist/css/mailpoet-email-editor.css');
    $this->wp->wpEnqueueMedia();

    $this->wp->wpEnqueueScript(
      'mailpoet_email_editor_v2',
      Env::$assetsUrl . '/dist/js/newsletter_editor_v2.js',
      [],
      Env::$version,
      true
    );

    if (function_exists('get_block_editor_settings')) {
      $settings = get_block_editor_settings([], new \WP_Block_Editor_Context());
    } else {
      $settings = $this->getEditorSettingsFallback();
    }

    $this->pageRenderer->displayPage('newsletter/editorv2.html', ['body' => $newsletterBody, 'settings' => $settings]);
  }

  /**
   * Set up Gutenberg editor settings - in case get_block_editor_settings is not defined
   */
  public function getEditorSettingsFallback(): array {
    global $editor_styles;

    $colorPalette = current((array)get_theme_support('editor-color-palette'));
    $fontSizes = current((array)get_theme_support('editor-font-sizes'));

    $maxUploadSize = wp_max_upload_size();
    if (!$maxUploadSize) {
      $maxUploadSize = 0;
    }

    // Editor Styles.
    $styles = array(
      array(
        'css' => file_get_contents(
          ABSPATH . WPINC . '/css/dist/editor/editor-styles.css'
        ),
      ),
    );

    $localeFontFamily = esc_html_x( 'Noto Serif', 'CSS Font Family for Editor Font' );
    $styles[] = array(
      'css' => "body { font-family: '$localeFontFamily' }",
    );

    if ($editor_styles && current_theme_supports( 'editor-styles' ) ) {
      foreach ( $editor_styles as $style ) {
        if ( preg_match( '~^(https?:)?//~', $style ) ) {
          $response = wp_remote_get( $style );
          if ( ! is_wp_error( $response ) ) {
            $styles[] = array(
              'css' => wp_remote_retrieve_body( $response ),
            );
          }
        } else {
          $file = get_theme_file_path( $style );
          if ( is_file( $file ) ) {
            $styles[] = array(
              'css' => file_get_contents( $file ),
              'baseURL' => get_theme_file_uri( $style ),
            );
          }
        }
      }
    }

    $image_size_names = apply_filters(
      'image_size_names_choose',
      array(
        'thumbnail' => __( 'Thumbnail' ),
        'medium' => __( 'Medium' ),
        'large' => __( 'Large' ),
        'full' => __( 'Full Size' ),
      )
    );

    $available_image_sizes = array();
    foreach ( $image_size_names as $image_size_slug => $image_size_name ) {
      $available_image_sizes[] = array(
        'slug' => $image_size_slug,
        'name' => $image_size_name,
      );
    }

    /**
     * @psalm-suppress TooManyArguments
     */
    $editorSettings = array(
      'disableCustomColors' => get_theme_support('disable-custom-colors'),
      'disableCustomFontSizes' => get_theme_support('disable-custom-font-sizes'),
      'disablePostFormats' => !current_theme_supports('post-formats'),
      /** This filter is documented in wp-admin/edit-form-advanced.php */
      'isRTL' => is_rtl(),
      'autosaveInterval' => AUTOSAVE_INTERVAL,
      'maxUploadFileSize' => $maxUploadSize,
      'allowedMimeTypes' => [],
      'styles' => $styles,
      'imageSizes' => $available_image_sizes,
      'richEditingEnabled' => user_can_richedit(),
      'codeEditingEnabled' => false,
      '__experimentalCanUserUseUnfilteredHTML' => false,
      '__experimentalBlockPatterns' => [],
      '__experimentalBlockPatternCategories' => [],
    );

    if ( false !== $colorPalette ) {
      $editorSettings['colors'] = $colorPalette;
    }

    if ( false !== $fontSizes ) {
      $editorSettings['fontSizes'] = $fontSizes;
    }

    return $editorSettings;
  }
}
