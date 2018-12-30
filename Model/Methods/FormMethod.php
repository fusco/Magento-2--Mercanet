<?php
/**
 * Cmsbox.fr Magento 2 Payment module (https://www.cmsbox.fr)
 *
 * Copyright (c) 2017 Cmsbox.fr (https://www.cmsbox.fr)
 * Author: David Fiaty | contact@cmsbox.fr
 *
 * License GNU/GPL V3 https://www.gnu.org/licenses/gpl-3.0.en.html
 */

namespace Cmsbox\Mercanet\Model\Methods;

use Magento\Framework\DataObject;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Payment\Model\Method\AbstractMethod;
use Cmsbox\Mercanet\Gateway\Config\Core;
use Cmsbox\Mercanet\Helper\Tools;

class FormMethod extends AbstractMethod {

    protected $_code;
    protected $_isInitializeNeeded = true;
    protected $_isGateway = true;
    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_canCancel = true;
    protected $_canCapturePartial = true;
    protected $_canVoid = true;
    protected $_canUseInternal = false;
    protected $_canUseCheckout = true;
    protected $_canRefund = true;
    protected $_canRefundInvoicePartial = true;
    protected $backendAuthSession;
    protected $cart;
    protected $urlBuilder;
    protected $_objectManager;
    protected $invoiceSender;
    protected $transactionFactory;
    protected $customerSession;
    protected $checkoutSession;
    protected $checkoutData;
    protected $quoteRepository;
    protected $quoteManagement;
    protected $orderSender;
    protected $sessionQuote;
    protected $transactionService;
    protected $remoteService;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\ObjectManagerInterface $objectManager, 
        \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender,
        \Magento\Framework\DB\TransactionFactory $transactionFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Checkout\Helper\Data $checkoutData,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Quote\Api\CartManagementInterface $quoteManagement,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data
        );
        $this->urlBuilder         = $urlBuilder;
        $this->backendAuthSession = $backendAuthSession;
        $this->cart               = $cart;
        $this->_objectManager     = $objectManager;
        $this->invoiceSender      = $invoiceSender;
        $this->transactionFactory = $transactionFactory;
        $this->customerSession    = $customerSession;
        $this->checkoutSession    = $checkoutSession;
        $this->checkoutData       = $checkoutData;
        $this->quoteRepository    = $quoteRepository;
        $this->quoteManagement    = $quoteManagement;
        $this->orderSender        = $orderSender;
        $this->sessionQuote       = $sessionQuote;
        $this->_code              = Core::methodId(get_class());
    }

    /**
     * Check whether method is available
     *
     * @param \Magento\Quote\Api\Data\CartInterface|\Magento\Quote\Model\Quote|null $quote
     * @return bool
     */
    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        return parent::isAvailable($quote) && null !== $quote;
    }

    /**
     * Check whether method is enabled in config
     *
     * @param \Magento\Quote\Model\Quote|null $quote
     * @return bool
     */
    public function isAvailableInConfig($quote = null)
    {
        return parent::isAvailable($quote);
    }

    /**
     * Returns the card request data.
     *
     * @return string
     */
    public static function getRequestData($config, $methodId) {
        // Prepare the parameters array
        $entity = $config->cart->getQuote();
        $params = [
            'amount' => $config->processor->formatAmount($entity->getGrandTotal()),
            'currencyCode' => $config->processor->convertCurrencyToCurrencyCode(Tools::getCurrencyCode($entity), $config),
            'merchantId' => $config->processor->getMerchantId($config),
            'customerId' => $entity->getCustomerId(),
            'transactionReference' => $config->processor->getTransactionReference(),
            'orderId' => Tools::getIncrementId($entity),
            'keyVersion' => $config->params[Core::moduleId()][Core::KEY_VERSION],
            'captureMode' => $config->params[$methodId][Core::KEY_CAPTURE_MODE],
            'captureDay' => (string) $config->params[$methodId][Core::KEY_CAPTURE_DAY],
            'orderChannel' => 'INTERNET',
            // Todo - Get interface version
            //'interfaceVersion' => $this->getInterfaceVersionCard()
        ];

        // Sort the parameters
        $params = Tools::multiSort($params);

        // Add the seal
        $params['seal'] = $config->processor->getSeal($params, $config, $exclude = ['keyVersion']);

        // Return the parameters
        return $params;
    }

    /**
     * Determines if the method is active.
     *
     * @return bool
     */
    public static function isOn($config, $methodId) {
        // Get the quote entity
        $entity = $config->cart->getQuote();

        // Check the currency status
        $currencyAccepted = in_array(
            $entity->getQuoteCurrencyCode(),
            explode(',', $config->params[Core::moduleId()][Core::KEY_ACCEPTED_CURRENCIES])
        );

        // Check the billing country status
        $countryBillingAccepted = in_array(
            $entity->getBillingAddress()->getCountryId(),
            explode(',', $config->params[Core::moduleId()][Core::KEY_ACCEPTED_COUNTRIES_BILLING])
        );

        // Check the shipping country status
        $countryShippingAccepted = in_array(
            $entity->getShippingAddress()->getCountryId(),
            explode(',', $config->params[Core::moduleId()][Core::KEY_ACCEPTED_COUNTRIES_SHIPPING])
        );

        return (int) (((int)  $config->params[$methodId]['active'] == 1)
        && $currencyAccepted
        && $countryBillingAccepted);
        // todo - check why this option not saving
        //&& $countryShippingAccepted;
    }
}