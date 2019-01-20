/**
 * Cmsbox.fr Magento 2 Payment module (https://www.cmsbox.fr)
 *
 * Copyright (c) 2017 Cmsbox.fr (https://www.cmsbox.fr)
 * Author: David Fiaty | contact@cmsbox.fr
 *
 * License GNU/GPL V3 https://www.gnu.org/licenses/gpl-3.0.en.html
 */

/*browser:true*/
/*global define*/

define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Cmsbox_Mercanet/js/view/payment/adapter',
        'mage/url',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/model/payment/additional-validators',
        'mage/translate',
        'Magento_Checkout/js/action/redirect-on-success',
        'Magento_Payment/js/model/credit-card-validation/validator',
        'mage/validation'
    ],
    function($, Component, Adapter, Url, FullScreenLoader, AdditionalValidators, t, RedirectOnSuccessAction) {
        'use strict';

        window.checkoutConfig.reloadOnBillingAddress = true;
        var code = 'form_method';

        return Component.extend({
            defaults: {
                template: Adapter.getName() + '/payment/' + code + '.phtml',
                moduleId: Adapter.getCode(),
                methodId: Adapter.getMethodId(code),
                config: Adapter.getPaymentConfig()[Adapter.getMethodId(code)],
                targetButton:  Adapter.getMethodId(code) + '_button',
                targetForm:  Adapter.getMethodId(code) + '_form',
                formControllerUrl: Url.build(Adapter.getCode() + '/request/form'),
                redirectAfterPlaceOrder: false
            },

            validateForm: function (form) {
                return $(form).validation() && $(form).validation('isValid');
            },

            /**
             * @returns {exports}
             */
            initialize: function() {
                this._super();
                Adapter.setEmailAddress();
                this.data = {'method': this.methodId};
            },

            initObservable: function() {
                this._super().observe([]);
                return this;
            },

            /**
             * @returns {string}
             */
            getCode: function() {
                return this.methodId;
            },

            /**
             * @returns {string}
             */
            getPaymentForm: function() {
                FullScreenLoader.startLoader();
                var self = this;
                $.ajax({
                    type: "POST",
                    url: self.formControllerUrl,
                    data: {task: 'block', method_id: self.methodId},
                    success: function(data) {
                        $('#' + self.targetForm).append(data.response);
                        FullScreenLoader.stopLoader();
                        Adapter.log(data);
                    },
                    error: function(request, status, error) {
                        Adapter.log(error);
                    }
                });
            },

            /**
             * @returns {bool}
             */
            isActive: function() {
                return this.config.active;
            },

            /**
             * @returns {string}
             */
            proceedWithSubmission: function() {
                // Assign self to this
                var self = this;

                // Prepare the selector
                var sel = '#' + this.targetForm;

                // Disable jQuery validate checks
                $(sel).validate().cancelSubmit = true;
                
                // Serialize the data
                var payLoad = $(sel).serializeArray();

                // Send the request
                $.ajax({
                    type: "POST",
                    url: self.formControllerUrl,
                    data: payLoad,
                    success: function(res) {
                        if (res.response === true) {
                            RedirectOnSuccessAction.execute();
                        }
                        else {
                            FullScreenLoader.stopLoader();
                            Adapter.showMessage('error', res.response);
                        }
                    },
                    error: function(request, status, error) {
                        FullScreenLoader.stopLoader();
                        Adapter.showMessage(t('The transaction could not be processed. Please check your details or contact the site administrator.'));
                    }
                });
            },

            /**
             * @returns {string}
             */
            beforePlaceOrder: function() {
                // Start the loader
                FullScreenLoader.startLoader();

                // Validate before submission
                if (AdditionalValidators.validate() && this.validateForm('#' + this.targetForm)) {
                    // Check cart and submit
                    this.proceedWithSubmission();
                }
                else {
                    FullScreenLoader.stopLoader();
                }
            }
        });
    }
);