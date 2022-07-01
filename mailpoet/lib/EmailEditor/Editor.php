<?php

namespace MailPoet\EmailEditor;

use MailPoet\Config\Env;
use MailPoet\DI\ContainerWrapper;

class Editor {
  const EMAIL_POST_TYPE = 'mailpoet_email';

  public function init() {
    $this->registerEmailPostType();
    add_action('enqueue_block_editor_assets', [$this, 'enqueueAssets']);
  }

  /**
   * @see https://developer.wordpress.org/reference/functions/register_post_type/
   * @return void
   */
  public function registerEmailPostType() {
    register_post_type( self::EMAIL_POST_TYPE,
      [
        'labels' => [
          'name' => __( 'Emails', 'mailpoet' ),
          'singular_name' => __( 'Emails', 'mailpoet' ),
        ],
        'has_archive' => false,
        'show_in_menu' => true,
        'show_ui' => true,
        'public' => false,
        'exclude_from_search' => true,
        'rewrite' => false,
        'show_in_rest' => true,
        'supports' => ['editor'],
      ]
    );
    // Temporary meta value for storing relation to mailpoet newsletter
    register_post_meta(self::EMAIL_POST_TYPE, 'mp_newsletter', ['type' => 'integer']);
  }

  public function enqueueAssets() {
    $src = Env::$assetsUrl . '/dist/js/newsletter_editor_v2_edit_post.js';
    wp_enqueue_script('mailpoet-email-editor', $src, [], false, true);
  }
}
