define(function(require) {
    'use strict';

    var AddressView;
    var _ = require('underscore');
    var BaseComponent = require('oroui/js/app/views/base/view');

    AddressView = BaseComponent.extend({
        /**
         * @inheritDoc
         */
        initialize: function(options) {
            this.$addressSelector = this.$el.find(options.selectors.address);
            this.$fieldsContainer = this.$el.find(options.selectors.fieldsContainer);
            this.$regionSelector = this.$el.find(options.selectors.region);

            this.needCheckAddressTypes = options.selectors.hasOwnProperty('shipToBillingCheckbox');
            if (this.needCheckAddressTypes) {
                this.typesMapping = this.$addressSelector.data('addresses-types');
                this.$shipToBillingCheckbox = this.$el.find(options.selectors.shipToBillingCheckbox);
                this.$shipToBillingCheckbox.on('change', _.bind(this._handleShipToBillingAddressCheckbox, this));
                this.shipToBillingContainer = this.$shipToBillingCheckbox.closest('fieldset');
            }

            this.$addressSelector.on('change', _.bind(this._onAddressChanged, this));
            this.$regionSelector.on('change', _.bind(this._onRegionListChanged, this));

            if (this.$fieldsContainer.find('.notification_error').length) {
                this.$fieldsContainer.removeClass('hidden');
            }

            this._onAddressChanged();
            this._handleShipToBillingAddressCheckbox()
        },

        _handleShipToBillingAddressCheckbox: function(e){
            var disabled = Boolean(this.$shipToBillingCheckbox.attr('checked'));
            if (!disabled && this._isFormVisible()) {
                this._showForm();
            } else {
                this._hideForm(true);
                this.$addressSelector.parent().find('.chosen-single').focus();
            }
            this.$addressSelector.attr('disabled', disabled);
            this.$addressSelector.inputWidget('refresh')
        },

        _onAddressChanged: function(e) {
            if (this._isFormVisible()) {
                this._showForm();
            } else {
                this._hideForm();
            }
        },

        _isFormVisible: function(){
            return this.$addressSelector.val() === '0';
        },

        _showForm: function(){
            if (this.needCheckAddressTypes) {
                this.shipToBillingContainer.removeClass('hidden');
            }

            this.$fieldsContainer.removeClass('hidden');
        },

        _hideForm: function(showCheckbox){
            if (this.needCheckAddressTypes) {
                if (showCheckbox || _.indexOf(this.typesMapping[this.$addressSelector.val()], 'shipping') > -1) {
                    this.shipToBillingContainer.removeClass('hidden');
                } else {
                    this.$shipToBillingCheckbox.prop('checked', false);
                    this.$shipToBillingCheckbox.trigger('change');
                    this.shipToBillingContainer.addClass('hidden');
                }
            }

            this.$fieldsContainer.addClass('hidden');
        },

        _setAddressSelectorState: function(state){
            this.$addressSelector.attr('disabled', state);
            this.$addressSelector.inputWidget('refresh');
        },

        _onRegionListChanged: function(e) {
            this.$regionSelector.chosen('destroy');
            this.$regionSelector.chosen({disable_search_threshold: 10});
        }
    });

    return AddressView;
});
