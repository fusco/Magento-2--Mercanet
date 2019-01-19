<?php
/**
 * Cmsbox.fr Magento 2 Payment module (https://www.cmsbox.fr)
 *
 * Copyright (c) 2017 Cmsbox.fr (https://www.cmsbox.fr)
 * Author: David Fiaty | contact@cmsbox.fr
 *
 * License GNU/GPL V3 https://www.gnu.org/licenses/gpl-3.0.en.html
 */

namespace Cmsbox\Mercanet\Model\Service;

use Magento\Sales\Model\Order;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Payment\Transaction;
use Cmsbox\Mercanet\Gateway\Config\Config;
use Cmsbox\Mercanet\Helper\Tools;
use Cmsbox\Mercanet\Gateway\Http\Client;
use Cmsbox\Mercanet\Gateway\Processor\Connector;

class RemoteHandlerService {

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Tools
     */
    protected $tools;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var Connector
     */
    protected $connector;

    /**
     * RemoteHandlerService constructor.
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        Config $config,
        Tools $tools,
        Client $client,
        Connector $connector
    ) {
        $this->orderRepository    = $orderRepository;
        $this->config             = $config;
        $this->tools              = $tools;
        $this->client             = $client;
        $this->connector          = $connector;
    }

    /**
     * Capture a transaction remotely.
     */
    public function captureRemoteTransaction($transaction, $amount, $payment = false) {
        // Prepare the request URL
        $url = $this->config->getApiUrl() . 'charges/' . $transaction->getTxnId() . '/capture';

        // Get the order
        $order = $this->orderRepository->get($transaction->getOrderId());

        // Get the track id
        $trackId = $order->getIncrementId();

        // Prepare the request parameters
        $params = [
            'value' => $this->tools->formatAmount($amount),
            'trackId' => $trackId
        ]; 

        // Send the request
        $response = $this->client->getPostResponse($url, $params);

        // Process the response
        if ($this->tools->isChargeSuccess($response)) {
            // Update the void transaction
            if ($payment) {
                $payment->setTransactionId($response['id']);
                $payment->setParentTransactionId($transaction->getTxnId());
                $payment->setIsTransactionClosed(1);
                $payment->save();
            }

            return true;
        }
       
        return false;
    }

    /**
     * Void a transaction remotely.
     */
    public function voidRemoteTransaction($transaction, $amount, $payment = false) {
        // Prepare the request URL
        $url = $this->config->getApiUrl() . 'charges/' . $transaction->getTxnId() . '/void';

        // Get the order
        $order = $this->orderRepository->get($transaction->getOrderId());

        // Get the track id
        $trackId = $order->getIncrementId();

        // Prepare the request parameters
        $params = [
            'value' => $this->tools->formatAmount($amount),
            'trackId' => $trackId
        ]; 

        // Send the request
        $response = $this->client->getPostResponse($url, $params);

        // Process the response
        if ($this->tools->isChargeSuccess($response)) {
            // Update the void transaction
            if ($payment) {
                $payment->setTransactionId($response['id']);
                $payment->setParentTransactionId($transaction->getTxnId());
                $payment->setIsTransactionClosed(1);
                $payment->save();
            }

            return true;
        }
       
        return false;
    }

    /**
     * Refund a transaction remotely.
     */
    public function refundRemoteTransaction($transaction, $amount, $payment = false) {
        // Prepare the request URL
        $url = $this->config->getApiUrl() . 'charges/' . $transaction->getTxnId() . '/refund';

        // Get the order
        $order = $this->orderRepository->get($transaction->getOrderId());

        // Get the track id
        $trackId = $order->getIncrementId();

        // Prepare the request parameters
        $params = [
            'value' => $this->tools->formatAmount($amount),
            'trackId' => $trackId
        ]; 

        // Send the request
        $response = $this->client->getPostResponse($url, $params);

        // Process the response
        if ($this->tools->isChargeSuccess($response)) {
            // Update the refund transaction
            if ($payment) {
               $payment->setTransactionId($response['id']);
               $payment->setParentTransactionId($transaction->getTxnId());
               $payment->setIsTransactionClosed(1);
               $payment->save();
            }

            return true;
        }
       
        return false;
    }
}