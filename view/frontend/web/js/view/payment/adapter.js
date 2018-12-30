/**
 * Cmsbox.fr Magento 2 Payment module (https://www.cmsbox.fr)
 *
 * Copyright (c) 2017 Cmsbox.fr (https://www.cmsbox.fr)
 * Author: David Fiaty | contact@cmsbox.fr
 *
 * License GNU/GPL V3 https://www.gnu.org/licenses/gpl-3.0.en.html
 */

define([
    'jquery',
    'Magento_Ui/js/model/messageList',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/checkout-data',
    'mage/cookies'
], function($, GlobalMessageList, Quote, CheckoutData) {
    'use strict';

    return {

        /**
         * Get payment configuration array.
         * @returns {Array}
         */
        getPaymentConfig: function() {
            return window.checkoutConfig.payment['cmsbox_mercanet'];
        },
        
        /**
         * Get payment code.
         * @returns {String}
         */
        getCode: function() {
            return this.getPaymentConfig()['module_id'];
        },

        /**
         * Get payment name.
         * @returns {String}
         */
        getName: function() {
            return this.getPaymentConfig()['module_name'];
        },

        /**
         * Get payment method id.
         * @returns {string}
         */
        getMethodId: function(methodId) {
            return this.getCode() + '_' + methodId;
        },

        /**
         * @returns {string}
         */
        getEmailAddress: function() {
            return window.checkoutConfig.customerData.email || Quote.guestEmail || CheckoutData.getValidatedEmailValue();
        },

        /**
         * @returns {void}
         */
        setEmailAddress: function() {
            var email = this.getEmailAddress();
            var cookieName = this.getPaymentConfig()['email_cookie_name'];
            $.cookie(cookieName, email);
        },

        /**
         * @returns {string}
         */
        getQuoteValue: function() {
            return (Quote.getTotals()().grand_total * 100).toFixed(2);
        },

        /**
         * Show error message
         *
         * @param {String} errorMessage
         */
        showError: function(errorMessage) {
            GlobalMessageList.addErrorMessage({
                message: errorMessage
            });
        },

        /**
         * Log data to the browser console
         */
        log: function(data) {
            var isDebugMode = JSON.parse(this.getPaymentConfig(this.getCode())['debug']);
            var output = this.getCode() + ':' + JSON.stringify(data);
            if (isDebugMode) console.log(output);
        }       
    };
});