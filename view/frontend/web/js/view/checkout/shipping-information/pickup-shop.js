define(
    [
        'jquery',
        'uiComponent',
        'ko',
        'mage/translate'
    ], function (
        $,
        Component,
        ko,
        $t
    ) {
        return Component.extend(
            {
                defaults: {
                    template: 'Montapacking_MontaCheckout/shipping-information/pickup-shop',
                    parcelShopAddress: ko.observable(),
                    deliveryInformation: ko.observableArray([]),
                    deliveryOptions: ko.observableArray([]),
                },

                initObservable: function () {
                    window.monta_plugin_pickup = this;

                    let checkoutConfig = window.checkoutConfig;
                    let montapacking = checkoutConfig.quoteData.montapacking_montacheckout_data;

                    if (montapacking !== undefined) {
                        montapacking = JSON.parse(montapacking);

                        let delivery_information = montapacking.additional_info[0];
                        this.deliveryInformation(delivery_information);

                        let delivery_options = montapacking.details[0].options;
                        let additional_info = [];

                        $(delivery_options).each(
                            function (key, value) {
                                if (value == 'SignatureOnDelivery') {
                                    additional_info.push($t("Signature on delivery"));
                                } else if (value == 'NoNeighbour') {
                                    additional_info.push($t("No delivery at neighbour"));
                                } else if (value == 'EveningDelivery') {
                                    additional_info.push($t("Evening delivery"));
                                } else {
                                    additional_info.push(value);
                                }
                            }
                        );

                        this.deliveryOptions(additional_info);
                    }

                    this.isSelected = ko.computed(
                        function () {
                            let isSelected = false;

                            if (window.monta_plugin_pickup.parcelShopAddress() !== null) {
                                isSelected = true;
                            }

                            return isSelected;
                        }, this
                    );

                    this._super();

                    return this;
                }
            }
        );
    }
);
