<?php
/**
 * Cmsbox.fr Magento 2 Payment module (https://www.cmsbox.fr)
 *
 * Copyright (c) 2017 Cmsbox.fr (https://www.cmsbox.fr)
 * Author: David Fiaty | contact@cmsbox.fr
 *
 * License GNU/GPL V3 https://www.gnu.org/licenses/gpl-3.0.en.html
 */

namespace Cmsbox\Mercanet\Model\Adminhtml\Source;

use Magento\Framework\Option\ArrayInterface;
use Magento\Sales\Model\Order\Payment\Transaction;

class InvoiceCreation implements ArrayInterface {

    /**
     * Possible environment types
     *
     * @return array
     */
    public function toOptionArray() {
        return [
            [
                'value' => Transaction::TYPE_CAPTURE,
                'label' => __('Capture')
            ],
            [
                'value' => Transaction::TYPE_AUTH,
                'label' => 'Authorisation'
            ],    
        ];
    }

}