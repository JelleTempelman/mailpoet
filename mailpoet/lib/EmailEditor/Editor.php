<?php

namespace MailPoet\EmailEditor;

class Editor {
  const EMAIL_POST_TYPE = 'mailpoet_email';

  public function init() {
    $this->registerEmailPostType();
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
  }
}
