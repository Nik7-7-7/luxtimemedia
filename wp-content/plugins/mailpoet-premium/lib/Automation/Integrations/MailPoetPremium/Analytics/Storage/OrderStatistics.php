<?php declare(strict_types = 1);

namespace MailPoet\Premium\Automation\Integrations\MailPoetPremium\Analytics\Storage;

if (!defined('ABSPATH')) exit;


use DateTimeImmutable;
use InvalidArgumentException;
use MailPoet\Automation\Engine\Exceptions;
use MailPoet\Automation\Integrations\MailPoet\Analytics\Entities\Query;
use MailPoet\Automation\Integrations\WooCommerce\WooCommerce;
use MailPoet\Entities\NewsletterEntity;

/**
 * @phpstan-type RawOrderType array{created_at: string, newsletter_id: int, order_id: int, total: float, subscriber_id: int, first_name:string, last_name:string, email:string, subject:string, status:string}
 */

class OrderStatistics {
  /** @var WooCommerce */
  private $wooCommerce;

  /** @var string[] */
  private $validOrderByValues = ['created_at', 'last_name', 'subject', 'status', 'revenue'];

  public function __construct(
    WooCommerce $wooCommerce
  ) {
    $this->wooCommerce = $wooCommerce;
  }

  /**
   * @param NewsletterEntity[] $newsletters
   * @param Query $query
   * @return RawOrderType[]
   */
  public function getOrdersForNewsletters(
    array $newsletters,
    Query $query
  ): array {
    if (!$newsletters) {
      return [];
    }
    $from = $query->getAfter();
    $to = $query->getBefore();
    $limit = $query->getLimit();
    $offset = max(0, ($query->getPage() - 1) * $query->getLimit());
    $orderBy = !empty($query->getOrderBy()) ? $query->getOrderBy() : 'createdAt';
    $order = $query->getOrderDirection() === 'asc' ? 'asc' : 'desc';

    if (!in_array($orderBy, $this->validOrderByValues, true)) {
      throw new InvalidArgumentException('Invalid orderBy parameter');
    }
    $result = ($this->wooCommerce->isWooCommerceCustomOrdersTableEnabled()) ?
      $this->getOrdersForNewslettersFromHpos($newsletters, $from, $to, $limit, $offset, $orderBy, $order) :
      $this->getOrdersForNewslettersFromLegacy($newsletters, $from, $to, $limit, $offset, $orderBy, $order);
    return is_array($result) ? $result : [];
  }

  /**
   * @param NewsletterEntity[] $newsletters
   * @param Query $query
   * @return int
   */
  public function getLastCount(
    array $newsletters,
    Query $query
  ): int {
    if (!$newsletters) {
      return 0;
    }
    $from = $query->getAfter();
    $to = $query->getBefore();
    $result = ($this->wooCommerce->isWooCommerceCustomOrdersTableEnabled()) ?
      $this->getOrdersForNewslettersFromHpos($newsletters, $from, $to, 0, 0, '', '', true) :
      $this->getOrdersForNewslettersFromLegacy($newsletters, $from, $to, 0, 0, '', '', true);
    return !is_int($result) ? 0 : $result;
  }

  /**
   * @param NewsletterEntity[] $newsletters
   * @param DateTimeImmutable $from
   * @param DateTimeImmutable $to
   * @param int $limit
   * @param int $offset
   * @param string $orderBy
   * @param string $order
   * @param bool $count
   * @return RawOrderType[] | int
   */
  private function getOrdersForNewslettersFromHpos(
    array $newsletters,
    DateTimeImmutable $from,
    DateTimeImmutable $to,
    int $limit = 100,
    int $offset = 0,
    string $orderBy = 'createdAt',
    string $order = 'desc',
    bool $count = false
  ) {
    global $wpdb;

    switch ($orderBy) {
      case 'last_name':
        $orderBy = 'subscriber.last_name';
        break;
      case 'subject':
        $orderBy = 'newsletter.subject';
        break;
      case 'status':
        $orderBy = 'order.status';
        break;
      case 'revenue':
        $orderBy = 'revenue.order_price_total';
        break;
      case 'created_at':
      default:
        $orderBy = 'revenue.created_at';
        break;
    }

    $newsletterIds = array_map(function ($newsletter) {
      return $newsletter->getId();
    }, $newsletters);

    $sqlSelect = $count
      ? 'COUNT(`revenue`.`id`) as `count`'
      : '
        `revenue`.`created_at`,
        `revenue`.`newsletter_id`,
        `revenue`.`order_id`,
        `revenue`.`order_price_total` AS `total`,
        `revenue`.`subscriber_id`,
        `subscriber`.`first_name`,
        `subscriber`.`last_name`,
        `subscriber`.`email`,
        `newsletter`.`subject`,
        `order`.`status`
      ';

    /** @var RawOrderType[] $result */
    $result = $wpdb->get_results(
      $wpdb->prepare(
        '
          SELECT ' . $sqlSelect . /* phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- The statement is safe. */ '
          FROM %i as `revenue`
          INNER JOIN %i as `order` ON `revenue`.order_id = `order`.ID
          INNER JOIN %i as `subscriber` ON `subscriber`.ID = `revenue`.subscriber_id
          INNER JOIN %i as `newsletter` ON `newsletter`.ID = `revenue`.newsletter_id
          WHERE revenue.created_at BETWEEN %s AND %s
          AND revenue.newsletter_id IN (' . implode(',', array_fill(0, count($newsletterIds), '%d')) . ')
        ',
        array_merge(
          [
            $wpdb->prefix . 'mailpoet_statistics_woocommerce_purchases',
            $wpdb->prefix . 'wc_orders',
            $wpdb->prefix . 'mailpoet_subscribers',
            $wpdb->prefix . 'mailpoet_newsletters',
            $from->format('Y-m-d H:i:s'),
            $to->format('Y-m-d H:i:s'),
          ],
          $newsletterIds
        )
      )
      . (!$count ? (" ORDER BY $orderBy $order, order.id $order, revenue.id $order " . $wpdb->prepare(' LIMIT %d, %d', $offset, $limit)) : ''),
      ARRAY_A
    );

    // phpcs:ignore Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps
    $error = $wpdb->last_error;
    if ($error) {
      throw Exceptions::databaseError($error);
    }

    if (!$count) {
      return is_array($result) ? $result : [];
    }
    return is_array($result) && isset($result[0]['count']) ? (int)$result[0]['count'] : 0;
  }

  /**
   * @param NewsletterEntity[] $newsletters
   * @param DateTimeImmutable $from
   * @param DateTimeImmutable $to
   * @param int $limit
   * @param int $offset
   * @param string $orderBy
   * @param string $order
   * @param bool $count
   * @return RawOrderType[] | int
   */
  private function getOrdersForNewslettersFromLegacy(
    array $newsletters,
    DateTimeImmutable $from,
    DateTimeImmutable $to,
    int $limit = 100,
    int $offset = 0,
    string $orderBy = 'createdAt',
    string $order = 'desc',
    bool $count = false
  ) {
    global $wpdb;

    switch ($orderBy) {
      case 'last_name':
        $orderBy = 'subscriber.last_name';
        break;
      case 'subject':
        $orderBy = 'newsletter.subject';
        break;
      case 'status':
        $orderBy = 'order.post_status';
        break;
      case 'revenue':
        $orderBy = 'revenue.order_price_total';
        break;
      case 'created_at':
      default:
        $orderBy = 'revenue.created_at';
        break;
    }

    $newsletterIds = array_map(function ($newsletter) {
      return $newsletter->getId();
    }, $newsletters);

    $sqlSelect = $count
      ? 'COUNT(`revenue`.`id`) as `count`'
      : '
        `revenue`.`created_at`,
        `revenue`.`newsletter_id`,
        `revenue`.`order_id`,
        `revenue`.`order_price_total` AS `total`,
        `revenue`.`subscriber_id`,
        `subscriber`.`first_name`,
        `subscriber`.`last_name`,
        `subscriber`.`email`,
        `newsletter`.`subject`,
        `order`.`post_status` as `status`
      ';

    /** @var RawOrderType[] $result */
    $result = $wpdb->get_results(
      $wpdb->prepare(
        '
          SELECT ' . $sqlSelect . /* phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- The statement is safe. */ '
          FROM %i as `revenue`
          INNER JOIN %i as `order` ON `revenue`.order_id = `order`.ID
          INNER JOIN %i as `subscriber` ON `subscriber`.ID = `revenue`.subscriber_id
          INNER JOIN %i as `newsletter` ON `newsletter`.ID = `revenue`.newsletter_id
          WHERE revenue.created_at BETWEEN %s AND %s
          AND revenue.newsletter_id IN (' . implode(',', array_fill(0, count($newsletterIds), '%d')) . ')
        ',
        array_merge(
          [
            $wpdb->prefix . 'mailpoet_statistics_woocommerce_purchases',
            $wpdb->posts,
            $wpdb->prefix . 'mailpoet_subscribers',
            $wpdb->prefix . 'mailpoet_newsletters',
            $from->format('Y-m-d H:i:s'),
            $to->format('Y-m-d H:i:s'),
          ],
          $newsletterIds
        )
      )
      . (!$count ? (" ORDER BY $orderBy $order, order.id $order, revenue.id $order " . $wpdb->prepare(' LIMIT %d, %d', $offset, $limit)) : ''),
      ARRAY_A
    );

    // phpcs:ignore Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps
    $error = $wpdb->last_error;
    if ($error) {
      throw Exceptions::databaseError($error);
    }

    if (!$count) {
      return is_array($result) ? $result : [];
    }

    return is_array($result) && isset($result[0]['count']) ? (int)$result[0]['count'] : 0;
  }
}
