<?php
/**
 * Cmsbox.fr Magento 2 Payment module (https://www.cmsbox.fr)
 *
 * Copyright (c) 2017 Cmsbox.fr (https://www.cmsbox.fr)
 * Author: David Fiaty | contact@cmsbox.fr
 *
 * License GNU/GPL V3 https://www.gnu.org/licenses/gpl-3.0.en.html
 */

namespace Cmsbox\Mercanet\Plugin;

use Closure;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Payment\State\CommandInterface;
use Magento\Sales\Model\Order;
use Cmsbox\Mercanet\Helper\Tools;
use Cmsbox\Mercanet\Gateway\Config\Config;
use Cmsbox\Mercanet\Gateway\Config\Core;

class OrderStatePlugin {

    /**
     * @var Tools
     */
    protected $tools;

    /**
     * @var Config
     */
    protected $config;

    /**
     * OrderStatePlugin constructor.
     */
    public function __construct(
        Tools $tools,
        Config $config
    ) {
        $this->tools         = $tools;
        $this->config = $config;
    }

    public function aroundExecute(
        CommandInterface $subject, 
        Closure $proceed, 
        OrderPaymentInterface $payment, 
        $amount, 
        OrderInterface $order
    ) {
        // Prepare the result
        $result = $proceed($payment, $amount, $order);

        // Build the module id from the payment method
        $methodCode = $payment->getMethodInstance()->getCode();
        $members = explode('_', $methodCode);
        $moduleId = isset($members[0]) && isset($members[1]) 
        ? $members[0] . $members[1] : '';

        // Check the payment method and update order status
        if (!empty($moduleId) && $moduleId == Core::moduleId()) {
            if ($order->getState() == Order::STATE_PROCESSING) {
                $order->setStatus($this->config->params[Core::moduleId()][Core::KEY_ORDER_STATUS_CAPTURED]);
            }            
        }

        return $result;
    }
}
