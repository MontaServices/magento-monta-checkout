define(
    [
        'ko',
        'Magento_Checkout/js/model/quote',
        'jquery',
        'Magento_Customer/js/model/customer'
    ], function (
        ko,
        quote,
        $,
        customer
    ) {
        'use strict';

        var address = {
                postcode    : null,
                country     : null,
                street      : null,
                city        : null,
                firstname   : null,
                lastname    : null,
                telephone   : null,
                housenumber : null,
                housenumberaddition : null
            },
            countryCode,
            timer,
            allFieldsExists = true,
            valueUpdateNotifier = ko.observable(null);

        if ($("input[name*='postalCode']").length > 0) {
            var fields = [
                "input[name*='streetNumber']",
                "input[name*='strNumberAddition",
                "input[name*='postalCode']",
                "select[name*='country_id']",
                "input[name*='street[0]']",
                "input[name*='street[1]']",
                "input[name*='street[2]']",
                "input[name*='city']",
                "input[name*='postcode']",
            ];
        } else {
            var fields = [
                "input[name*='street[0]']",
                "input[name*='street[1]']",
                "input[name*='street[2]']",
                "input[name*='city']",
                "input[name*='postcode']",
                "select[name*='country_id']",
            ];
        }


        /**
         * Without cookie data Magento is not observing the fields so the AddressFinder is never triggered.
         * The Timeout is needed so it gives the Notifier the chance to retrieve the correct country code,
         * and not the default value.
         */


        (function() {
            function checkState() {

            	var countryCheck = "";
            	if(typeof $("select[name*='country_id']").val() !== "undefined")
            	{
            		countryCheck = $("select[name*='country_id']").val();
            	}

                if ($("input[name*='postalCode']").length > 0 && countryCheck == "NL") {

                    if ($("input[name*='postalCode']").length > 0 && $("#montapacking-plugin").length) {
                        var success = true; // do something to check the state
                    } else {
                        var success = false; // do something to check the state
                    }

                } else {

                    if ($("input[name*='postcode']").length > 0 && $("#montapacking-plugin").length) {
                        var success = true; // do something to check the state
                    } else {
                        var success = false; // do something to check the state
                    }
                }


                if (!success) {
                    setTimeout(checkState, 50);
                }
            }
            setTimeout(checkState, 50);
        })();


        $(document).on('change', fields.join(','), function () {

            // Clear timeout if exists.
            if (typeof timer !== 'undefined') {
                clearTimeout(timer);
            }

            timer = setTimeout(
                function () {
                    countryCode = $("select[name*='country_id']").val();
                    valueUpdateNotifier.notifySubscribers();
                }, 500
            );
        } );

        /**
         * Collect the needed information from the quote
         */

        return ko.computed(
            function () {

                valueUpdateNotifier();

                /**
                 * The street is not always available on the first run.
                 */
                var shippingAddress = quote.shippingAddress();
                var quoteStreet = [];
                if (shippingAddress) {
                    quoteStreet = (typeof shippingAddress.street === 'undefined') ? [] : shippingAddress.street;
                }

                if (customer.isLoggedIn() && shippingAddress && quoteStreet.length > 0) {
                    address = {
                        street: quoteStreet,
                        postcode: shippingAddress.postcode,
                        city: shippingAddress.city,
                        lastname: shippingAddress.lastname,
                        firstname: shippingAddress.firstname,
                        telephone: shippingAddress.telephone,
                        country: shippingAddress.countryId
                    };

                    return address;
                }

                allFieldsExists = true;

                if ($("input[name*='postalCode']").length > 0) {
               	 allFieldsExists = true;
               } else {
	                $.each(
	                    fields, function () {
	                        /**
	                         * Second street may not exist and is therefor not required and should only be observed.
	                         */
	                        if (!$(this).length && this !== "input[name*='street[1]']" && this !== "input[name*='street[2]']") {
	                            allFieldsExists = false;
	                            return false;
	                        }
	                    }
	                );
               }


                if (!allFieldsExists) {
                    return null;
                }

                /**
                 * Unfortunately Magento does not always fill all fields, so get them ourselves.
                 */


                var countryCheck = "";
            	if(typeof $("select[name*='country_id']").val() !== "undefined")
            	{
            		countryCheck = $("select[name*='country_id']").val();
            	}

                if ($("input[name*='postalCode']").length > 0 && countryCheck == "NL") {
                    address.postcode   = $("input[name*='postalCode']").val();
                    address.housenumber   = $("input[name*='streetNumber']").val();
                    address.housenumberaddition   = $("input[name*='strNumberAddition']").val();
                } else {

                    address.postcode   = $("input[name*='postcode']").val();

                    address.street = {
                        0 : $("input[name*='street[0]']").val(),
                        1 : $("input[name*='street[1]']").val(),
                        2 : $("input[name*='street[2]']").val()
                    };
                }

                address.city   = $("input[name*='city']").val();
                address.firstname  = $("input[name*='firstname']").val();
                address.lastname   = $("input[name*='lastname']").val();
                address.telephone  = $("input[name*='telephone']").val();

                if (!address.country || address.country !== countryCode) {
                    address.country = $("select[name*='country_id']").val();
                }

                if (!address.country || !address.postcode) {
                    return false;
                }

                return address;


            }.bind(this)
        );
    }
);
