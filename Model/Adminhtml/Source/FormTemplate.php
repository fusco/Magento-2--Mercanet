<?php
/**
 * Naxero.com Magento 2 Mercanet Payment.
 *
 * PHP version 7
 *
 * @category  Naxero
 * @package   Mercanet
 * @author    Naxero Development Team <contact@naxero.com>
 * @copyright 2019 Naxero.com all rights reserved
 * @license   https://opensource.org/licenses/mit-license.html MIT License
 * @link      https://www.naxero.com
 */

namespace Naxero\Mercanet\Model\Adminhtml\Source;

use Naxero\Mercanet\Gateway\Processor\Connector;

class FormTemplate implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Possible form templates
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 'template_1',
                'label' => __('Template 1'),
            ],
            [
                'value' => 'template_2',
                'label' => __('Template 2'),
            ],
            [
                'value' => 'template_3',
                'label' => __('Template 3'),
            ],
        ];
    }
}
