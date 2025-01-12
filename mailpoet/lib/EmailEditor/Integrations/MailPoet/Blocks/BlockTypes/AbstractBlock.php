<?php declare(strict_types = 1);

namespace MailPoet\EmailEditor\Integrations\MailPoet\Blocks\BlockTypes;

use MailPoet\Config\Env;

abstract class AbstractBlock {
  protected $namespace = 'mailpoet';
  protected $blockName = '';

  public function initialize() {
    $this->registerAssets();
    $this->registerBlockType();
  }

  protected function getBlockType(): string {
    return $this->namespace . '/' . $this->blockName;
  }

  protected function parseRenderCallbackAttributes($attributes): array {
    return is_a($attributes, 'WP_Block') ? $attributes->attributes : $attributes;
  }

  protected function registerAssets() {
    if (null !== $this->getEditorScript()) {
      // @todo Would usually just register, but the editor_script are not being loaded in the custom editor.
      wp_enqueue_script(
        $this->getEditorScript('handle'),
        $this->getEditorScript('path'),
        $this->getEditorScript('dependencies'),
        $this->getEditorScript('version'),
        true
      );
    }

    if (null !== $this->getEditorStyle()) {
      // @todo Would usually just register, but the editor_script are not being loaded in the custom editor.
      wp_enqueue_style(
        $this->getEditorStyle('handle'),
        $this->getEditorStyle('path'),
        [],
        $this->getEditorScript('version'),
        'all'
      );
    }
  }

  protected function registerBlockType() {
    if (\WP_Block_Type_Registry::get_instance()->is_registered($this->getBlockType())) {
      return;
    }
    $metadata_path = Env::$assetsPath . '/js/src/email-editor/blocks/' . $this->blockName . '/block.json';
    $block_settings = [
        'render_callback' => [$this, 'render'],
        'editor_script' => $this->getEditorScript('handle'),
        'editor_style' => $this->getEditorStyle('handle'),
    ];
    register_block_type_from_metadata(
      $metadata_path,
      $block_settings
    );
  }

  protected function getEditorScript($key = null) {
    $asset_file_path = Env::$assetsPath . '/dist/js/email-editor-blocks/' . $this->blockName . '-block.asset.php';

    if (!file_exists($asset_file_path)) {
      return null;
    }

    $asset_file = require $asset_file_path;
    $script = [
        'handle' => 'mailpoet-' . $this->blockName . '-block',
        'path' => Env::$assetsUrl . '/dist/js/email-editor-blocks/' . $this->blockName . '-block.js',
        'dependencies' => $asset_file['dependencies'],
        'version' => $asset_file['version'],
    ];
    return $key ? $script[$key] : $script;
  }

  protected function getEditorStyle($key = null) {
    $path = Env::$assetsUrl . '/dist/js/email-editor-blocks/style-' . $this->blockName . '-block.css';

    if (!file_exists($path)) {
      return null;
    }

    $style = [
        'handle' => 'mailpoet-' . $this->blockName . '-block',
        'path' => Env::$assetsUrl . '/dist/js/email-editor-blocks/style-' . $this->blockName . '-block.css',
    ];
    return $key ? $style[$key] : $style;
  }

  abstract public function render($attributes, $content, $block);
}
