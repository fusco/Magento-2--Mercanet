<?php
/**
 * Cmsbox.fr Magento 2 Mercanet Payment.
 *
 * PHP version 7
 *
 * @category  Cmsbox
 * @package   Mercanet
 * @author    Cmsbox Development Team <contact@cmsbox.fr>
 * @copyright 2019 Cmsbox.fr all rights reserved
 * @license   https://opensource.org/licenses/mit-license.html MIT License
 * @link      https://www.cmsbox.fr
 */

namespace Cmsbox\Mercanet\Model\Service;

class FormHandlerService
{

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Watchdog
     */
    protected $watchdog;

    /**
     * @var PaymentConfig
     */
    protected $paymentConfig;

    /**
     * FormHandlerService constructor.
     */
    public function __construct(
        \Cmsbox\Mercanet\Gateway\Config\Config $config,
        \Cmsbox\Mercanet\Helper\Watchdog $watchdog,
        \Magento\Payment\Model\Config $paymentConfig
    ) {
        $this->config             = $config;
        $this->watchdog           = $watchdog;
        $this->paymentConfig      = $paymentConfig;
    }

    /**
     * Retrieve credit card expire months
     *
     * @return array
     */
    public function getMonths()
    {
        return array_merge(
            [__('Month')],
            $this->paymentConfig->getMonths()
        );
    }

    /**
     * Retrieve credit card expire years
     *
     * @return array
     */
    public function getYears()
    {
        return array_merge(
            [__('Year')],
            $this->paymentConfig->getYears()
        );
    }
}
