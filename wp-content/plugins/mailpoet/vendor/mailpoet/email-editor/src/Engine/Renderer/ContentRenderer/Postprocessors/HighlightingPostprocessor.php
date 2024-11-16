<?php declare(strict_types = 1);
namespace MailPoet\EmailEditor\Engine\Renderer\ContentRenderer\Postprocessors;
if (!defined('ABSPATH')) exit;
class HighlightingPostprocessor implements Postprocessor {
 public function postprocess(string $html): string {
 return str_replace(
 ['<mark', '</mark>'],
 ['<span', '</span>'],
 $html
 );
 }
}
