<?php // phpcs:ignore SlevomatCodingStandard.TypeHints.DeclareStrictTypes.DeclareStrictTypesMissing

namespace MailPoet\Premium\API\JSON\v1\ResponseBuilders;

if (!defined('ABSPATH')) exit;


use MailPoet\Entities\NewsletterEntity;
use MailPoet\Entities\NewsletterLinkEntity;
use MailPoet\Entities\StatisticsClickEntity;
use MailPoet\Entities\StatisticsOpenEntity;
use MailPoet\Entities\UserAgentEntity;
use MailPoet\Newsletter\Url as NewsletterUrl;
use MailPoet\Premium\Subscriber\Stats\SubscriberNewsletterStats;
use MailPoet\WooCommerce\Helper as WCHelper;

class SubscriberDetailedStatsResponseBuilder {
  const DATE_FORMAT = 'Y-m-d H:i:s';

  /** @var WCHelper */
  private $wooCommerce;

  /** @var NewsletterUrl */
  private $newsletterUrl;

  public function __construct(
    NewsletterUrl $newsletterUrl,
    WCHelper $wooCommerce
  ) {
    $this->newsletterUrl = $newsletterUrl;
    $this->wooCommerce = $wooCommerce;
  }

  /**
   * @param SubscriberNewsletterStats[] $newslettersStats
   * @return array<int, array<string, mixed>>
   */
  public function build(array $newslettersStats): array {
    $response = [];

    foreach ($newslettersStats as $stats) {
      $item = $this->buildNewsletter($stats->getNewsletter());
      $openStats = $stats->getOpen();

      if ($openStats instanceof StatisticsOpenEntity) {
        $item['actions'][] = $this->buildOpen($openStats);
      }

      foreach ($stats->getClicks() as $click) {
        $item['actions'][] = $this->buildClick($click);
      }
      $response[] = $item;
    }
    return $response;
  }

  /**
   * @param NewsletterEntity $newsletter
   *
   * @return array{id: int|null, preview_url: string, subject: string, sent_at: non-empty-string|null, actions: array{}}
   */
  private function buildNewsletter(NewsletterEntity $newsletter): array {
    $sentAt = $newsletter->getSentAt();
    $previewUrl = $this->newsletterUrl->getViewInBrowserUrl(
      $newsletter,
      null,
      in_array($newsletter->getStatus(), [NewsletterEntity::STATUS_SENT, NewsletterEntity::STATUS_SENDING], true)
        ? $newsletter->getLatestQueue()
        : null
    );
    return [
      'id' => $newsletter->getId(),
      'preview_url' => $previewUrl,
      'subject' => $newsletter->getSubject(),
      'campaign_name' => $newsletter->getCampaignName(),
      'sent_at' => $sentAt ? $sentAt->format(self::DATE_FORMAT) : null,
      'actions' => [],
    ];
  }

  /**
   * @param StatisticsOpenEntity $open
   *
   * @return array<string, int|string|null>
   */
  private function buildOpen(StatisticsOpenEntity $open): array {
    return [
      'id' => $open->getId(),
      'type' => $open->getUserAgentType() === UserAgentEntity::USER_AGENT_TYPE_MACHINE ? 'machine-open' : 'open',
      'created_at' => ($createdAt = $open->getCreatedAt()) ? $createdAt->format(self::DATE_FORMAT) : null,
    ];
  }

  /**
   * @param StatisticsClickEntity $click
   *
   * @return array<string, array<int, array<string, mixed>>|int|string|null>
   */
  private function buildClick(StatisticsClickEntity $click): array {
    $link = $click->getLink();
    $linkUrl = ($link instanceof NewsletterLinkEntity) ? $link->getUrl() : '';
    $purchases = [];
    foreach ($click->getWooCommercePurchases() as $purchase) {
      if (!in_array($purchase->getStatus(), $this->wooCommerce->getPurchaseStates(), true)) {
        continue;
      }
      $order = $this->wooCommerce->wcGetOrder($purchase->getOrderId());
      $purchases[] = [
        'id' => $purchase->getId(),
        'created_at' => ($createdAt = $purchase->getCreatedAt()) ? $createdAt->format(self::DATE_FORMAT) : null,
        'order_id' => $purchase->getOrderId(),
        'order_url' => $order instanceof \WC_Order ? $order->get_edit_order_url() : null,
        'revenue' => $this->wooCommerce->getRawPrice(
          $purchase->getOrderPriceTotal(),
          ['currency' => $purchase->getOrderCurrency()]
        ),
      ];
    }
    return [
      'id' => $click->getId(),
      'type' => 'click',
      'created_at' => ($createdAt = $click->getCreatedAt()) ? $createdAt->format(self::DATE_FORMAT) : null,
      'count' => $click->getCount(),
      'url' => $linkUrl,
      'purchases' => $purchases,
    ];
  }
}
