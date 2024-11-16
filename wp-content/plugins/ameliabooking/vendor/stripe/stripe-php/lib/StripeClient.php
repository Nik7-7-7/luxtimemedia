<?php

// File generated from our OpenAPI spec

namespace AmeliaStripe;

/**
 * Client used to send requests to Stripe's API.
 *
 * @property \AmeliaStripe\Service\AccountLinkService $accountLinks
 * @property \AmeliaStripe\Service\AccountService $accounts
 * @property \AmeliaStripe\Service\ApplePayDomainService $applePayDomains
 * @property \AmeliaStripe\Service\ApplicationFeeService $applicationFees
 * @property \AmeliaStripe\Service\Apps\AppsServiceFactory $apps
 * @property \AmeliaStripe\Service\BalanceService $balance
 * @property \AmeliaStripe\Service\BalanceTransactionService $balanceTransactions
 * @property \AmeliaStripe\Service\BillingPortal\BillingPortalServiceFactory $billingPortal
 * @property \AmeliaStripe\Service\ChargeService $charges
 * @property \AmeliaStripe\Service\Checkout\CheckoutServiceFactory $checkout
 * @property \AmeliaStripe\Service\CountrySpecService $countrySpecs
 * @property \AmeliaStripe\Service\CouponService $coupons
 * @property \AmeliaStripe\Service\CreditNoteService $creditNotes
 * @property \AmeliaStripe\Service\CustomerService $customers
 * @property \AmeliaStripe\Service\DisputeService $disputes
 * @property \AmeliaStripe\Service\EphemeralKeyService $ephemeralKeys
 * @property \AmeliaStripe\Service\EventService $events
 * @property \AmeliaStripe\Service\ExchangeRateService $exchangeRates
 * @property \AmeliaStripe\Service\FileLinkService $fileLinks
 * @property \AmeliaStripe\Service\FileService $files
 * @property \AmeliaStripe\Service\FinancialConnections\FinancialConnectionsServiceFactory $financialConnections
 * @property \AmeliaStripe\Service\Identity\IdentityServiceFactory $identity
 * @property \AmeliaStripe\Service\InvoiceItemService $invoiceItems
 * @property \AmeliaStripe\Service\InvoiceService $invoices
 * @property \AmeliaStripe\Service\Issuing\IssuingServiceFactory $issuing
 * @property \AmeliaStripe\Service\MandateService $mandates
 * @property \AmeliaStripe\Service\OAuthService $oauth
 * @property \AmeliaStripe\Service\OrderService $orders
 * @property \AmeliaStripe\Service\PaymentIntentService $paymentIntents
 * @property \AmeliaStripe\Service\PaymentLinkService $paymentLinks
 * @property \AmeliaStripe\Service\PaymentMethodService $paymentMethods
 * @property \AmeliaStripe\Service\PayoutService $payouts
 * @property \AmeliaStripe\Service\PlanService $plans
 * @property \AmeliaStripe\Service\PriceService $prices
 * @property \AmeliaStripe\Service\ProductService $products
 * @property \AmeliaStripe\Service\PromotionCodeService $promotionCodes
 * @property \AmeliaStripe\Service\QuoteService $quotes
 * @property \AmeliaStripe\Service\Radar\RadarServiceFactory $radar
 * @property \AmeliaStripe\Service\RefundService $refunds
 * @property \AmeliaStripe\Service\Reporting\ReportingServiceFactory $reporting
 * @property \AmeliaStripe\Service\ReviewService $reviews
 * @property \AmeliaStripe\Service\SetupAttemptService $setupAttempts
 * @property \AmeliaStripe\Service\SetupIntentService $setupIntents
 * @property \AmeliaStripe\Service\ShippingRateService $shippingRates
 * @property \AmeliaStripe\Service\Sigma\SigmaServiceFactory $sigma
 * @property \AmeliaStripe\Service\SkuService $skus
 * @property \AmeliaStripe\Service\SourceService $sources
 * @property \AmeliaStripe\Service\SubscriptionItemService $subscriptionItems
 * @property \AmeliaStripe\Service\SubscriptionScheduleService $subscriptionSchedules
 * @property \AmeliaStripe\Service\SubscriptionService $subscriptions
 * @property \AmeliaStripe\Service\TaxCodeService $taxCodes
 * @property \AmeliaStripe\Service\TaxRateService $taxRates
 * @property \AmeliaStripe\Service\Terminal\TerminalServiceFactory $terminal
 * @property \AmeliaStripe\Service\TestHelpers\TestHelpersServiceFactory $testHelpers
 * @property \AmeliaStripe\Service\TokenService $tokens
 * @property \AmeliaStripe\Service\TopupService $topups
 * @property \AmeliaStripe\Service\TransferService $transfers
 * @property \AmeliaStripe\Service\Treasury\TreasuryServiceFactory $treasury
 * @property \AmeliaStripe\Service\WebhookEndpointService $webhookEndpoints
 */
class StripeClient extends BaseStripeClient
{
    /**
     * @var \AmeliaStripe\Service\CoreServiceFactory
     */
    private $coreServiceFactory;

    public function __get($name)
    {
        if (null === $this->coreServiceFactory) {
            $this->coreServiceFactory = new \AmeliaStripe\Service\CoreServiceFactory($this);
        }

        return $this->coreServiceFactory->__get($name);
    }
}
