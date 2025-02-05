<?php declare(strict_types = 1);

namespace MailPoet\Premium\Automation\Integrations\MailPoetPremium\Analytics\Endpoints;

if (!defined('ABSPATH')) exit;


use MailPoet\API\REST\Request;
use MailPoet\API\REST\Response;
use MailPoet\Automation\Engine\API\Endpoint;
use MailPoet\Automation\Engine\Exceptions;
use MailPoet\Automation\Engine\Storage\AutomationStorage;
use MailPoet\Automation\Integrations\MailPoet\Analytics\Entities\Query;
use MailPoet\Premium\Automation\Integrations\MailPoetPremium\Analytics\Controller\OrderController;
use MailPoet\Validator\Builder;

class OrderEndpoint extends Endpoint {
  /** @var AutomationStorage */
  private $automationStorage;

  /** @var OrderController */
  private $orderController;

  public function __construct(
    AutomationStorage $automationStorage,
    OrderController $orderController
  ) {
    $this->automationStorage = $automationStorage;
    $this->orderController = $orderController;
  }

  public static function getRequestSchema(): array {
    return [
      'id' => Builder::integer()->required(),
      'query' => Query::getRequestSchema(),
    ];
  }

  public function handle(Request $request): Response {
    $id = absint($request->getParam('id'));
    $automation = $this->automationStorage->getAutomation($id);
    if (!$automation) {
      throw Exceptions::automationNotFound($id);
    }

    $query = Query::fromRequest($request);
    $result = $this->orderController->getOrdersForAutomation($automation, $query);
    return new Response($result);
  }
}
