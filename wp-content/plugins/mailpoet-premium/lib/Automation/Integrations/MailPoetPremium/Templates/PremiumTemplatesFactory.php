<?php declare(strict_types = 1);

namespace MailPoet\Premium\Automation\Integrations\MailPoetPremium\Templates;

if (!defined('ABSPATH')) exit;


use MailPoet\Automation\Engine\Data\Automation;
use MailPoet\Automation\Engine\Data\AutomationTemplate;
use MailPoet\Automation\Engine\Data\NextStep;
use MailPoet\Automation\Engine\Templates\AutomationBuilder;
use MailPoet\Automation\Integrations\WooCommerce\WooCommerce;

class PremiumTemplatesFactory {
  /** @var AutomationBuilder */
  private $builder;

  /** @var WooCommerce */
  private $woocommerce;

  public function __construct(
    AutomationBuilder $builder,
    WooCommerce $woocommerce
  ) {
    $this->builder = $builder;
    $this->woocommerce = $woocommerce;
  }

  /** @return AutomationTemplate[] */
  public function createTemplates(): array {
    $templates = [
      $this->createSubscriberWelcomeSeriesTemplate(),
      $this->createUserWelcomeSeriesTemplate(),
    ];

    if ($this->woocommerce->isWooCommerceActive()) {
      $templates[] = $this->createThankLoyalCustomersTemplate();
      $templates[] = $this->createWinBackCustomersTemplate();
      $templates[] = $this->createAbandonedCartCampaignTemplate();
    }

    return $templates;
  }

  private function createSubscriberWelcomeSeriesTemplate(): AutomationTemplate {
    return new AutomationTemplate(
      'subscriber-welcome-series',
      'welcome',
      __('Welcome series for new subscribers', 'mailpoet-premium'),
      __(
        'Welcome new subscribers and start building a relationship with them. Send an email immediately after someone subscribes to your list to introduce your brand and a follow-up two days later to keep the conversation going.',
        'mailpoet-premium'
      ),
      function (): Automation {
        return $this->builder->createFromSequence(
          __('Welcome series for new subscribers', 'mailpoet-premium'),
          [
            ['key' => 'mailpoet:someone-subscribes'],
            ['key' => 'mailpoet:send-email', 'args' => ['name' => __('Welcome email', 'mailpoet-premium')]],
            ['key' => 'core:delay', 'args' => ['delay' => 2, 'delay_type' => 'DAYS']],
            ['key' => 'mailpoet:send-email', 'args' => ['name' => __('Follow-up email', 'mailpoet-premium')]],
          ],
          [
            'mailpoet:run-once-per-subscriber' => true,
          ]
        );
      },
      [
        'automationSteps' => 2,
      ],
      AutomationTemplate::TYPE_DEFAULT
    );
  }

  private function createUserWelcomeSeriesTemplate(): AutomationTemplate {
    return new AutomationTemplate(
      'user-welcome-series',
      'welcome',
      __('Welcome series for new WordPress users', 'mailpoet-premium'),
      __(
        'Welcome new WordPress users to your site. Send an email immediately after a WordPress user registers. Send a follow-up email two days later with more in-depth information.',
        'mailpoet-premium'
      ),
      function (): Automation {
        return $this->builder->createFromSequence(
          __('Welcome series for new WordPress users', 'mailpoet-premium'),
          [
            ['key' => 'mailpoet:wp-user-registered'],
            ['key' => 'mailpoet:send-email', 'args' => ['name' => __('Welcome email', 'mailpoet-premium')]],
            ['key' => 'core:delay', 'args' => ['delay' => 2, 'delay_type' => 'DAYS']],
            ['key' => 'mailpoet:send-email', 'args' => ['name' => __('Follow-up email', 'mailpoet-premium')]],
          ],
          [
            'mailpoet:run-once-per-subscriber' => true,
          ]
        );
      },
      [
        'automationSteps' => 2,
      ],
      AutomationTemplate::TYPE_DEFAULT
    );
  }

  private function createThankLoyalCustomersTemplate(): AutomationTemplate {
    return new AutomationTemplate(
      'thank-loyal-customers',
      'woocommerce',
      __('Thank loyal customers', 'mailpoet-premium'),
      __(
        'These are your most important customers. Make them feel special by sending a thank you note for supporting your brand.',
        'mailpoet-premium'
      ),
      function (): Automation {
        return $this->builder->createFromSequence(
          __('Thank loyal customers', 'mailpoet-premium'),
          [
            [
              'key' => 'woocommerce:order-completed',
              'filters' => [
                'operator' => 'and',
                'groups' => [
                  [
                    'operator' => 'and',
                    'filters' => [
                      [
                        'field' => 'woocommerce:customer:order-count',
                        'condition' => 'greater-than',
                        'value' => 5,
                        'params' => ['in_the_last' => ['number' => 365, 'unit' => 'days']],
                      ],
                    ],
                  ],
                ],
              ],
            ],
            ['key' => 'core:delay', 'args' => ['delay' => 1, 'delay_type' => 'DAYS']],
            [
              'key' => 'mailpoet:send-email',
              'args' => [
                'name' => __('Thank you for your loyalty', 'mailpoet-premium'),
                'subject' => __('Thank you for your loyalty', 'mailpoet-premium'),
              ],
            ],
          ],
        );
      },
      [
        'automationSteps' => 1,
      ],
      // This template is available from the Basic plan, although judged solely by capabilities,
      // it would've been eligible for the Free plan. Keeping TYPE_PREMIUM allows us to exclude
      // it from the Free plan (= if tier is 0 and it's a premium template, it will be excluded).
      AutomationTemplate::TYPE_PREMIUM
    );
  }

  private function createWinBackCustomersTemplate(): AutomationTemplate {
    return new AutomationTemplate(
      'win-back-customers',
      'woocommerce',
      __('Win-back customers', 'mailpoet-premium'),
      __(
        'Rekindle the relationship with past customers by reminding them of their favorite products and showcasing what’s new, encouraging a return to your brand.',
        'mailpoet-premium'
      ),
      function (): Automation {
        $automation = $this->builder->createFromSequence(
          __('Win-back customers', 'mailpoet-premium'),
          [
            ['key' => 'woocommerce:order-completed'],
            ['key' => 'core:delay', 'args' => ['delay' => 60, 'delay_type' => 'DAYS']],
            [
              'key' => 'core:if-else',
              'filters' => [
                'operator' => 'and',
                'groups' => [
                  [
                    'operator' => 'and',
                    'filters' => [
                      [
                        'field' => 'woocommerce:customer:order-count',
                        'condition' => 'equals',
                        'value' => 0,
                        'params' => ['in_the_last' => ['number' => 90, 'unit' => 'days']],
                      ],
                    ],
                  ],
                ],
              ],
            ],
            [
              'key' => 'mailpoet:send-email',
              'args' => [
                'name' => __('It’s been a while…', 'mailpoet-premium'),
                'subject' => __('It’s been a while…', 'mailpoet-premium'),
              ],
            ],
            ['key' => 'core:delay', 'args' => ['delay' => 15, 'delay_type' => 'DAYS']],
            [
              'key' => 'core:if-else',
              'filters' => [
                'operator' => 'and',
                'groups' => [
                  [
                    'operator' => 'and',
                    'filters' => [
                      [
                        'field' => 'woocommerce:customer:order-count',
                        'condition' => 'equals',
                        'value' => 0,
                        'params' => ['in_the_last' => ['number' => 15, 'unit' => 'days']],
                      ],
                    ],
                  ],
                ],
              ],
            ],
            [
              'key' => 'mailpoet:send-email',
              'args' => [
                'name' => __('We’ve missed you', 'mailpoet-premium'),
                'subject' => __('We’ve missed you', 'mailpoet-premium'),
              ],
            ],
          ]
        );

        foreach ($automation->getSteps() as $step) {
          if ($step->getKey() === 'core:if-else') {
            $step->setNextSteps(array_merge($step->getNextSteps(), [new NextStep(null)]));
          }
        }
        return $automation;
      },
      [
        'automationSteps' => 4,
      ],
      AutomationTemplate::TYPE_DEFAULT
    );
  }

  private function createAbandonedCartCampaignTemplate(): AutomationTemplate {
    return new AutomationTemplate(
      'abandoned-cart-campaign',
      'abandoned-cart',
      __('Abandoned cart campaign', 'mailpoet-premium'),
      __(
        'Encourage your potential customers to finalize their purchase when they have added items to their cart but haven’t finished the order yet. Offer a coupon code as a last resort to convert them to customers.',
        'mailpoet-premium'
      ),
      function (): Automation {
        $automation = $this->builder->createFromSequence(
          __('Abandoned cart campaign', 'mailpoet-premium'),
          [
            ['key' => 'woocommerce:abandoned-cart', 'args' => ['wait' => 60]],
            [
              'key' => 'mailpoet:send-email',
              'args' => [
                'name' => __('It looks like you left something behind…', 'mailpoet-premium'),
                'subject' => __('It looks like you left something behind…', 'mailpoet-premium'),
              ],
            ],
            ['key' => 'core:delay', 'args' => ['delay' => 23, 'delay_type' => 'HOURS']],
            [
              'key' => 'core:if-else',
              'filters' => [
                'operator' => 'and',
                'groups' => [
                  [
                    'operator' => 'and',
                    'filters' => [
                      [
                        'field' => 'woocommerce:customer:order-count',
                        'condition' => 'equals',
                        'value' => 0,
                        'params' => ['in_the_last' => ['number' => 1 , 'unit' => 'days']],
                      ],
                    ],
                  ],
                ],
              ],
            ],
            [
              'key' => 'mailpoet:send-email',
              'args' => [
                'name' => __('Your cart is waiting', 'mailpoet-premium'),
                'subject' => __('Your cart is waiting', 'mailpoet-premium'),
              ],
            ],
            ['key' => 'core:delay', 'args' => ['delay' => 1, 'delay_type' => 'DAYS']],
            [
              'key' => 'core:if-else',
              'filters' => [
                'operator' => 'and',
                'groups' => [
                  [
                    'operator' => 'and',
                    'filters' => [
                      [
                        'field' => 'woocommerce:customer:order-count',
                        'condition' => 'equals',
                        'value' => 0,
                        'params' => ['in_the_last' => ['number' => 1, 'unit' => 'days']],
                      ],
                      [
                        'field' => 'woocommerce:cart:cart-total',
                        'condition' => 'greater-than',
                        'value' => 100,
                      ],
                    ],
                  ],
                ],
              ],
            ],
            [
              'key' => 'mailpoet:send-email',
              'args' => [
                'name' => __('Your cart is waiting, and so is your 20% off!', 'mailpoet-premium'),
                'subject' => __('Your cart is waiting, and so is your 20% off!', 'mailpoet-premium'),
              ],
            ],
          ]
        );

        foreach ($automation->getSteps() as $step) {
          if ($step->getKey() === 'core:if-else') {
            $step->setNextSteps(array_merge($step->getNextSteps(), [new NextStep(null)]));
          }
        }
        return $automation;
      },
      [
        'automationSteps' => 5,
      ],
      AutomationTemplate::TYPE_DEFAULT
    );
  }
}
