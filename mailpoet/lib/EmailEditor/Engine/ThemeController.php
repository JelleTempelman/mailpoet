<?php declare(strict_types = 1);

namespace MailPoet\EmailEditor\Engine;

use MailPoet\EmailEditor\Engine\Renderer\Renderer;
use WP_Theme_JSON;
use WP_Theme_JSON_Resolver;

/**
 * E-mail editor works with own theme.json which defines settings for the editor and styles for the e-mail.
 * This class is responsible for accessing data defined by the theme.json.
 */
class ThemeController {
  private WP_Theme_JSON $coreTheme;
  private WP_Theme_JSON $baseTheme;

  public function __construct() {
    $this->coreTheme = WP_Theme_JSON_Resolver::get_core_data();
    $this->baseTheme = new WP_Theme_JSON((array)json_decode((string)file_get_contents(dirname(__FILE__) . '/theme.json'), true), 'default');
  }

  public function getTheme(): WP_Theme_JSON {
    $theme = new WP_Theme_JSON();
    $theme->merge($this->coreTheme);
    $theme->merge($this->baseTheme);

    if (Renderer::getTheme() !== null) {
      $theme->merge(Renderer::getTheme());
    }

    return apply_filters('mailpoet_email_editor_theme_json', $theme);
  }

  /**
   * @return array{
   *   spacing: array{
   *     blockGap: string,
   *     padding: array{bottom: string, left: string, right: string, top: string}
   *   },
   *   color: array{
   *     background: string
   *   },
   *   typography: array{
   *     fontFamily: string
   *   }
   * }
   */
  public function getStyles(): array {
    return $this->getTheme()->get_data()['styles'];
  }

  public function getSettings(): array {
    $emailEditorThemeSettings = $this->getTheme()->get_settings();
    $siteThemeSettings = WP_Theme_JSON_Resolver::get_theme_data()->get_settings();
    $emailEditorThemeSettings['color']['palette']['theme'] = [];
    if (isset($siteThemeSettings['color']['palette']['theme'])) {
      $emailEditorThemeSettings['color']['palette']['theme'] = $siteThemeSettings['color']['palette']['theme'];
    }
    return $emailEditorThemeSettings;
  }

  public function getStylesheetForRendering($post = null): string {
    $emailThemeSettings = $this->getSettings();

    $cssPresets = '';
    // Font family classes
    foreach ($emailThemeSettings['typography']['fontFamilies']['default'] as $fontFamily) {
      $cssPresets .= ".has-{$fontFamily['slug']}-font-family { font-family: {$fontFamily['fontFamily']}; } \n";
    }
    // Font size classes
    foreach ($emailThemeSettings['typography']['fontSizes']['default'] as $fontSize) {
      $cssPresets .= ".has-{$fontSize['slug']}-font-size { font-size: {$fontSize['size']}; } \n";
    }
    // Color palette classes
    $colorDefinitions = array_merge($emailThemeSettings['color']['palette']['theme'], $emailThemeSettings['color']['palette']['default']);
    foreach ($colorDefinitions as $color) {
      $cssPresets .= ".has-{$color['slug']}-color { color: {$color['color']}; } \n";
      $cssPresets .= ".has-{$color['slug']}-background-color { background-color: {$color['color']}; } \n";
      $cssPresets .= ".has-{$color['slug']}-border-color { border-color: {$color['color']}; } \n";
    }

    // Block specific styles
    $cssBlocks = '';
    $blocks = $this->getTheme()->get_styles_block_nodes();
    foreach ($blocks as $blockMetadata) {
      $cssBlocks .= $this->getTheme()->get_styles_for_block($blockMetadata);
    }

    // Element specific styles
    // Because the section styles is not a part of the output the `get_styles_block_nodes` method, we need to get it separately
    if ($post) {
      $postTheme = (array)get_post_meta($post->ID, 'mailpoet_email_theme', true);
      $postStyles = (array)($postTheme['styles'] ?? []);
      $postElements = $postStyles['elements'] ?? [];
    } else {
      $postElements = [];
    }
    $jsonElements = $this->getTheme()->get_raw_data()['styles']['elements'] ?? [];
    $elementsStyles = array_merge_recursive((array)$jsonElements, (array)$postElements);

    $cssElements = '';
    foreach ($elementsStyles as $key => $elementsStyle) {
      $selector = $key;

      if ($key === 'heading') {
        $selector = 'h1, h2, h3, h4, h5, h6';
      }

      if ($key === 'link') {
        // Target direct decendants of blocks to avoid styling buttons. :not() is not supported by the inliner.
        $selector = 'p > a, div > a, li > a';
      }

      if ($key === 'button') {
        $selector = '.wp-block-button';
      }

      $cssElements .= wp_style_engine_get_styles($elementsStyle, ['selector' => $selector])['css'];
    }

    $result = $cssPresets . $cssBlocks . $cssElements;
    // Because font-size can by defined by the clamp() function that is not supported in the e-mail clients, we need to replace it to the value.
    // Regular expression to match clamp() function and capture its max value
    $pattern = '/clamp\([^,]+,\s*[^,]+,\s*([^)]+)\)/';
    // Replace clamp() with its maximum value
    $result = (string)preg_replace($pattern, '$1', $result);
    return $result;
  }

  public function translateSlugToFontSize(string $fontSize): string {
    $settings = $this->getSettings();
    foreach ($settings['typography']['fontSizes']['default'] as $fontSizeDefinition) {
      if ($fontSizeDefinition['slug'] === $fontSize) {
        return $fontSizeDefinition['size'];
      }
    }
    return $fontSize;
  }

  public function translateSlugToColor(string $colorSlug): string {
    $settings = $this->getSettings();
    $colorDefinitions = array_merge($settings['color']['palette']['theme'], $settings['color']['palette']['default']);
    foreach ($colorDefinitions as $colorDefinition) {
      if ($colorDefinition['slug'] === $colorSlug) {
        return strtolower($colorDefinition['color']);
      }
    }
    return $colorSlug;
  }

  public function getVariablesValuesMap(): array {
    $variablesCss = $this->getTheme()->get_stylesheet(['variables']);
    $map = [];
    // Regular expression to match CSS variable definitions
    $pattern = '/--(.*?):\s*(.*?);/';

    if (preg_match_all($pattern, $variablesCss, $matches, PREG_SET_ORDER)) {
      foreach ($matches as $match) {
        // '--' . $match[1] is the variable name, $match[2] is the variable value
        $map['--' . $match[1]] = $match[2];
      }
    }

    return $map;
  }
}
