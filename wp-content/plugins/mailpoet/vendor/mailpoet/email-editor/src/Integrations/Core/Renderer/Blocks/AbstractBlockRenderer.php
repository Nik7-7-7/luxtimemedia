<?php declare(strict_types = 1);
namespace MailPoet\EmailEditor\Integrations\Core\Renderer\Blocks;
if (!defined('ABSPATH')) exit;
use MailPoet\EmailEditor\Engine\Renderer\ContentRenderer\BlockRenderer;
use MailPoet\EmailEditor\Engine\SettingsController;
use WP_Style_Engine;
abstract class AbstractBlockRenderer implements BlockRenderer {
 protected function getStylesFromBlock(array $block_styles, $skip_convert_vars = false) {
 $styles = wp_style_engine_get_styles($block_styles, ['convert_vars_to_classnames' => $skip_convert_vars]);
 return wp_parse_args($styles, [
 'css' => '',
 'declarations' => [],
 'classnames' => '',
 ]);
 }
 protected function compileCss(...$styles): string {
 return WP_Style_Engine::compile_css(array_merge(...$styles), '');
 }
 protected function addSpacer($content, $emailAttrs): string {
 $gapStyle = WP_Style_Engine::compile_css(array_intersect_key($emailAttrs, array_flip(['margin-top'])), '');
 $paddingStyle = WP_Style_Engine::compile_css(array_intersect_key($emailAttrs, array_flip(['padding-left', 'padding-right'])), '');
 if (!$gapStyle && !$paddingStyle) {
 return $content;
 }
 return sprintf(
 '<!--[if mso | IE]><table align="left" role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%%" style="%2$s"><tr><td style="%3$s"><![endif]-->
 <div class="email-block-layout" style="%2$s %3$s">%1$s</div>
 <!--[if mso | IE]></td></tr></table><![endif]-->',
 $content,
 esc_attr($gapStyle),
 esc_attr($paddingStyle)
 );
 }
 public function render(string $blockContent, array $parsedBlock, SettingsController $settingsController): string {
 return $this->addSpacer(
 $this->renderContent($blockContent, $parsedBlock, $settingsController),
 $parsedBlock['email_attrs'] ?? []
 );
 }
 abstract protected function renderContent(string $blockContent, array $parsedBlock, SettingsController $settingsController): string;
}
