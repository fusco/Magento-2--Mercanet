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

define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Naxero_Mercanet/js/model/agreement-validator',
    ],
    function (Component, additionalValidators, agreementValidator) {
        'use strict';
        additionalValidators.registerValidator(agreementValidator);
        return Component.extend({});
    }
);