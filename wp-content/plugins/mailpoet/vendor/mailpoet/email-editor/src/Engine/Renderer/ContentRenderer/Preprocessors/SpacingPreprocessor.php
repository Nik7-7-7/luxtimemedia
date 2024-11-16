<?php declare(strict_types = 1);
namespace MailPoet\EmailEditor\Engine\Renderer\ContentRenderer\Preprocessors;
if (!defined('ABSPATH')) exit;
class SpacingPreprocessor implements Preprocessor {
 public function preprocess(array $parsedBlocks, array $layout, array $styles): array {
 $parsedBlocks = $this->addBlockGaps($parsedBlocks, $styles['spacing']['blockGap'] ?? '', null);
 $parsedBlocks = $this->addBlockPadding(
 $parsedBlocks,
 $styles['spacing']['padding']['left'] ?? '',
 $styles['spacing']['padding']['right'] ?? '',
 );
 return $parsedBlocks;
 }
 private function addBlockGaps(array $parsedBlocks, string $gap = '', $parentBlock = null): array {
 foreach ($parsedBlocks as $key => $block) {
 $parentBlockName = $parentBlock['blockName'] ?? '';
 // Do not add a gap to the first child, or if the parent block is a buttons block (where buttons are side by side).
 if ($key !== 0 && $gap && $parentBlockName !== 'core/buttons') {
 $block['email_attrs']['margin-top'] = $gap;
 }
 $block['innerBlocks'] = $this->addBlockGaps($block['innerBlocks'] ?? [], $gap, $block);
 $parsedBlocks[$key] = $block;
 }
 return $parsedBlocks;
 }
 private function addBlockPadding(array $parsedBlocks, string $parentPaddingLeft, string $parentPaddingRight): array {
 foreach ($parsedBlocks as $key => $block) {
 $align = $block['attrs']['align'] ?? '';
 if ($align !== 'full') {
 $block['email_attrs']['padding-left'] = $parentPaddingLeft;
 $block['email_attrs']['padding-right'] = $parentPaddingRight;
 }
 $block['innerBlocks'] = $this->addBlockPadding(
 $block['innerBlocks'] ?? [],
 $block['attrs']['style']['spacing']['padding']['padding-left'] ?? '',
 $block['attrs']['style']['spacing']['padding']['padding-right'] ?? ''
 );
 $parsedBlocks[$key] = $block;
 }
 return $parsedBlocks;
 }
}
