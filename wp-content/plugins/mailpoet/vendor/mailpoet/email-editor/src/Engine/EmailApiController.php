<?php declare(strict_types = 1);
namespace MailPoet\EmailEditor\Engine;
if (!defined('ABSPATH')) exit;
use MailPoet\EmailEditor\Validator\Builder;
class EmailApiController {
 public function getEmailData(): array {
 // Here comes code getting Email specific data that will be passed on 'email_data' attribute
 return [];
 }
 public function saveEmailData(array $data, \WP_Post $emailPost): void {
 // Here comes code saving of Email specific data that will be passed on 'email_data' attribute
 }
 public function getEmailDataSchema(): array {
 return Builder::object()->toArray();
 }
}
