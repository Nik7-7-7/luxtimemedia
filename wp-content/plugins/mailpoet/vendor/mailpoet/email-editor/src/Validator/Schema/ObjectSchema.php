<?php declare(strict_types = 1);
namespace MailPoet\EmailEditor\Validator\Schema;
if (!defined('ABSPATH')) exit;
use MailPoet\EmailEditor\Validator\Schema;
// See: https://developer.wordpress.org/rest-api/extending-the-rest-api/schema/#objects
class ObjectSchema extends Schema {
 protected $schema = [
 'type' => 'object',
 ];
 public function properties(array $properties): self {
 return $this->updateSchemaProperty('properties', array_map(
 function (Schema $property) {
 return $property->toArray();
 },
 $properties
 ));
 }
 public function additionalProperties(Schema $schema): self {
 return $this->updateSchemaProperty('additionalProperties', $schema->toArray());
 }
 public function disableAdditionalProperties(): self {
 return $this->updateSchemaProperty('additionalProperties', false);
 }
 public function patternProperties(array $properties): self {
 $patternProperties = [];
 foreach ($properties as $key => $value) {
 $this->validatePattern($key);
 $patternProperties[$key] = $value->toArray();
 }
 return $this->updateSchemaProperty('patternProperties', $patternProperties);
 }
 public function minProperties(int $value): self {
 return $this->updateSchemaProperty('minProperties', $value);
 }
 public function maxProperties(int $value): self {
 return $this->updateSchemaProperty('maxProperties', $value);
 }
}
