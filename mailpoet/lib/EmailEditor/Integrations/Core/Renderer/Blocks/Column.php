<?php declare(strict_types = 1);

namespace MailPoet\EmailEditor\Integrations\Core\Renderer\Blocks;

use MailPoet\EmailEditor\Engine\Renderer\BlockRenderer;
use MailPoet\EmailEditor\Engine\SettingsController;
use MailPoet\EmailEditor\Integrations\Utils\DomDocumentHelper;

class Column implements BlockRenderer {
  public function render(string $blockContent, array $parsedBlock, SettingsController $settingsController): string {
    $content = '';
    foreach ($parsedBlock['innerBlocks'] ?? [] as $block) {
      $content .= render_block($block);
    }

    return str_replace(
      '{column_content}',
      $content,
      $this->getBlockWrapper($blockContent, $parsedBlock, $settingsController)
    );
  }

  /**
   * Based on MJML <mj-column>
   */
  private function getBlockWrapper(string $blockContent, array $parsedBlock, SettingsController $settingsController): string {
    // Getting individual border properties
    $borderColor = $parsedBlock['attrs']['style']['border']['color'] ?? '#000000';
    $borderWidth = $parsedBlock['attrs']['style']['border']['width'] ?? '0px';
    $borderRadius = $parsedBlock['attrs']['style']['border']['radius'] ?? '0px';
    // Because borders can by configured individually, we need to get each one of them and use the main border properties as fallback
    $borderBottomColor = $parsedBlock['attrs']['style']['border']['bottom']['color'] ?? $borderColor;
    $borderLeftColor = $parsedBlock['attrs']['style']['border']['left']['color'] ?? $borderColor;
    $borderRightColor = $parsedBlock['attrs']['style']['border']['right']['color'] ?? $borderColor;
    $borderTopColor = $parsedBlock['attrs']['style']['border']['top']['color'] ?? $borderColor;
    $borderBottomWidth = $parsedBlock['attrs']['style']['border']['bottom']['width'] ?? $borderWidth;
    $borderLeftWidth = $parsedBlock['attrs']['style']['border']['left']['width'] ?? $borderWidth;
    $borderRightWidth = $parsedBlock['attrs']['style']['border']['right']['width'] ?? $borderWidth;
    $borderTopWidth = $parsedBlock['attrs']['style']['border']['top']['width'] ?? $borderWidth;
    $borderBottomLeftRadius = $parsedBlock['attrs']['style']['border']['radius']['bottomLeft'] ?? $borderRadius;
    $borderBottomRightRadius = $parsedBlock['attrs']['style']['border']['radius']['bottomRight'] ?? $borderRadius;
    $borderTopLeftRadius = $parsedBlock['attrs']['style']['border']['radius']['topLeft'] ?? $borderRadius;
    $borderTopRightRadius = $parsedBlock['attrs']['style']['border']['radius']['topRight'] ?? $borderRadius;

    $width = $parsedBlock['email_attrs']['width'] ?? $settingsController->getLayoutWidthWithoutPadding();
    // Because width is primarily used for the max-width property, we need to add the left and right border width to it
    $width = $settingsController->parseNumberFromStringWithPixels($width);
    $width += $settingsController->parseNumberFromStringWithPixels($borderLeftWidth ?? '0px');
    $width += $settingsController->parseNumberFromStringWithPixels($borderRightWidth ?? '0px');
    $width = "{$width}px";
    $paddingBottom = $parsedBlock['attrs']['style']['spacing']['padding']['bottom'] ?? '0px';
    $paddingLeft = $parsedBlock['attrs']['style']['spacing']['padding']['left'] ?? '0px';
    $paddingRight = $parsedBlock['attrs']['style']['spacing']['padding']['right'] ?? '0px';
    $paddingTop = $parsedBlock['attrs']['style']['spacing']['padding']['top'] ?? '0px';

    $colorStyles = [];
    if (isset($parsedBlock['attrs']['style']['color']['background'])) {
      $colorStyles['background-color'] = $parsedBlock['attrs']['style']['color']['background'];
      $colorStyles['background'] = $parsedBlock['attrs']['style']['color']['background'];
    }
    if (isset($parsedBlock['attrs']['style']['color']['text'])) {
      $colorStyles['color'] = $parsedBlock['attrs']['style']['color']['text'];
    }

    $classes = (new DomDocumentHelper($blockContent))->getAttributeValueByTagName('div', 'class') ?? '';

    $verticalAlign = 'top';
    // Because `stretch` is not a valid value for the `vertical-align` property, we don't override the default value
    if (isset($parsedBlock['attrs']['verticalAlignment']) && $parsedBlock['attrs']['verticalAlignment'] !== 'stretch') {
      $verticalAlign = $parsedBlock['attrs']['verticalAlignment'];
    }

    $mainCellStyles = [
      'width' => $width,
      'vertical-align' => $verticalAlign,
    ];

    // The default column alignment is `stretch to fill` which means that we need to set the background color to the main cell
    // to create a feeling of a stretched column
    if (!isset($parsedBlock['attrs']['verticalAlignment']) || $parsedBlock['attrs']['verticalAlignment'] === 'stretch') {
      $mainCellStyles = array_merge($mainCellStyles, $colorStyles);
    }

    $borderStyles = '';
    if ($borderBottomWidth !== '0px') {
      $borderStyles .= 'border-bottom:' . $borderBottomWidth . ' solid ' . $borderBottomColor . ';';
    }
    if ($borderLeftWidth !== '0px') {
      $borderStyles .= 'border-left:' . $borderLeftWidth . ' solid ' . $borderLeftColor . ';';
    }
    if ($borderRightWidth !== '0px') {
      $borderStyles .= 'border-right:' . $borderRightWidth . ' solid ' . $borderRightColor . ';';
    }
    if ($borderTopWidth !== '0px') {
      $borderStyles .= 'border-top:' . $borderTopWidth . ' solid ' . $borderTopColor . ';';
    }
    if ($borderStyles !== '') {
      $borderStyles .= 'border-radius:' . $borderTopLeftRadius . ' ' . $borderTopRightRadius . ' ' . $borderBottomRightRadius . ' ' . $borderBottomLeftRadius . ';';
      $borderStyles .= 'box-sizing:border-box;';
    }

    return '
      <td class="block ' . esc_attr($classes) . '" style="' . esc_attr($borderStyles . $settingsController->convertStylesToString($mainCellStyles)) . '">
        <div class="email_column" style="width:100%;max-width:' . esc_attr($width) . ';font-size:0px;text-align:left;display:inline-block;">
          <table class="email_column ' . esc_attr($classes) . '" border="0" cellpadding="0" cellspacing="0" role="presentation" style="' . esc_attr($settingsController->convertStylesToString($colorStyles)) . ';min-width:100%;width:100%;max-width:' . esc_attr($width) . ';vertical-align:top;" width="' . esc_attr($width) . '">
            <tbody>
              <tr>
                <td align="left" style="font-size:0px;padding-left:' . esc_attr($paddingLeft) . ';padding-right:' . esc_attr($paddingRight) . ';padding-bottom:' . esc_attr($paddingBottom) . ';padding-top:' . esc_attr($paddingTop) . ';">
                  <div style="text-align:left;">{column_content}</div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </td>
    ';
  }
}
