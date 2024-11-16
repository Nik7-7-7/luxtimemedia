<?php declare(strict_types = 1);
namespace MailPoet\EmailEditor\Validator;
if (!defined('ABSPATH')) exit;
use function json_encode;
use function rest_get_allowed_schema_keywords;
abstract class Schema {
 protected $schema = [];
 public function nullable() {
 $type = $this->schema['type'] ?? ['null'];
 return $this->updateSchemaProperty('type', is_array($type) ? $type : [$type, 'null']);
 }
 public function nonNullable() {
 $type = $this->schema['type'] ?? null;
 return $type === null
 ? $this->unsetSchemaProperty('type')
 : $this->updateSchemaProperty('type', is_array($type) ? $type[0] : $type);
 }
 public function required() {
 return $this->updateSchemaProperty('required', true);
 }
 public function optional() {
 return $this->unsetSchemaProperty('required');
 }
 public function title(string $title) {
 return $this->updateSchemaProperty('title', $title);
 }
 public function description(string $description) {
 return $this->updateSchemaProperty('description', $description);
 }
 public function default($default) {
 return $this->updateSchemaProperty('default', $default);
 }
 public function field(string $name, $value) {
 if (in_array($name, $this->getReservedKeywords(), true)) {
 throw new \Exception("Field name '$name' is reserved");
 }
 return $this->updateSchemaProperty($name, $value);
 }
 public function toArray(): array {
 return $this->schema;
 }
 public function toString(): string {
 $json = json_encode($this->schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION);
 $error = json_last_error();
 if ($error || $json === false) {
 throw new \Exception(json_last_error_msg(), (string)$error);
 }
 return $json;
 }
 protected function updateSchemaProperty(string $name, $value) {
 $clone = clone $this;
 $clone->schema[$name] = $value;
 return $clone;
 }
 protected function unsetSchemaProperty(string $name) {
 $clone = clone $this;
 unset($clone->schema[$name]);
 return $clone;
 }
 protected function getReservedKeywords(): array {
 return rest_get_allowed_schema_keywords();
 }
 protected function validatePattern(string $pattern): void {
 $escaped = str_replace('#', '\\#', $pattern);
 $regex = "#$escaped#u";
 if (@preg_match($regex, '') === false) {
 throw new \Exception("Invalid regular expression '$regex'");
 }
 }
}
