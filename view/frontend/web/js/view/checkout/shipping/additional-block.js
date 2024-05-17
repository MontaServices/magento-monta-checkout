define(
    [
        'jquery',
        'uiComponent',
        'ko',
        'Magento_Checkout/js/model/quote',
        'Montapacking_MontaCheckout/js/helper/address-finder',
        'Montapacking_MontaCheckout/js/view/checkout/shipping-information/pickup-shop',
        'Magento_Checkout/js/action/set-shipping-information'
    ], function (
        $,
        Component,
        ko,
        quote,
        AddressFinder,
        pickupShop,
        setShippingInformationAction
    ) {

        'use strict';

        return Component.extend(
            {
                defaults: {
                    template: 'Montapacking_MontaCheckout/checkout/shipping/additional-block',
                    postcode: null,
                    country: null,
                    hasconnection: 'true',
                    urlPrefix: '',
                    deliveryServices: ko.observableArray([]),
                    standardDeliveryServices: ko.observableArray([]),
                    filteredDeliveryServices: ko.observableArray([]),
                    daysForSelect: ko.observableArray([]),
                    pickupServices: ko.observableArray([]),
                    deliveryFee: ko.observable(),
                    pickupFee: ko.observable(),
                    selectedShippers: ko.observable(),
                    selectedPickup: ko.observable(),
                    preferredShipper: ko.observable()
                },
                initObservable: function () {
                    //one step checkout solution, update buttons and quantity change are not working, so we are gonna hide this options
                    require([
                        'jquery',
                        'Magento_Ui/js/lib/view/utils/dom-observer',
                    ], function ($, $do) {
                        $(document).ready(function () {
                            $do.get('.product-item-details .details-qty', function (elem) {
                                //$(elem).removeClass('visible');
                                $(elem).find("input").attr('readonly', true);
                                $('.product-item-details .qtybuttons .remove').css('display', 'none');
                                $('.product-item-details .qtybuttons .add').css('display', 'none');
                            });
                        });
                    });

                    window.monta_plugin = this;

                    const url = new URL(window.location.href).toString();

                    let urlPrefix = '';

                    if (url.includes('/nl/')) {
                        urlPrefix = '/nl';
                    }

                    if (url.includes('/be/')) {
                        urlPrefix = '/be';
                    }

                    if (url.includes('/de/')) {
                        urlPrefix = '/de';
                    }

                    if (url.includes('/en/')) {
                        urlPrefix = '/en';
                    }

                    if (url.includes('/fr/')) {
                        urlPrefix = '/fr';
                    }

                    if (url.includes('/it/')) {
                        urlPrefix = '/it';
                    }

                    if (url.includes('/es/')) {
                        urlPrefix = '/es';
                    }

                    this.urlPrefix = urlPrefix;

                    this.selectedMethod = ko.computed(
                        function () {
                            const method = quote.shippingMethod();
                            return method != null ? method.carrier_code + '_' + method.method_code : null;
                        }, this
                    );

                    this.tabClasses = ko.computed(
                        function () {
                            return 'montapacking-tabs';
                        }, this
                    );

                    this._super().observe(
                        [
                            'hasconnection',
                            'postcode',
                            'street',
                            'country',
                            'deliveryServices',
                            'filteredDeliveryServices',
                            'standardDeliveryServices',
                            'daysForSelect',
                            'pickupPoints',
                            'preferredShipper'
                        ]
                    );

                    AddressFinder.subscribe(
                        function (address) {

                            if (typeof address == "undefined") {
                                return;
                            }

                            if (!address || JSON.stringify(address) === $("#old_address").val()) {
                                return;
                            }

                            $("#montapacking_longitude").val("");
                            $("#montapacking_latitude").val("");
                            $("#montapacking_addresschangedsincelastmapload").val('true');
                            $("#montapacking_addresschangedsincelastlonglatcall").val('true');

                            this.deliveryFee(null);
                            this.pickupFee(null);

                            this.getDeliveryServices(address.street, address.postcode, address.city, address.country, address.housenumber, address.housenumberaddition, false);

                            window.monta_plugin.toggleTab('.montapacking-tab-pickup', '.montapacking-tab-delivery', '.pickup-services', '.delivery-services', false, true, this.addressChanged);

                            // fill old adress field
                            const existCondition = setInterval(function () {
                                if ($("#old_address").length) {
                                    clearInterval(existCondition);
                                    $("#old_address").val(JSON.stringify(address));
                                }
                            }, 100);

                        }.bind(this)
                    );

                    window.monta_plugin.loadPopup();

                    return this;
                },

                /**
                 * Retrieve LONG LAT
                 */
                getLongLat: function (street, postcode, city, country, housenumber, housenumberaddition, longlat) {
                    this.setLoading();

                    $.ajax(
                        {
                            method: 'GET',
                            url: this.urlPrefix + '/montacheckout/deliveryoptions/longlat',
                            type: 'jsonp',
                            showLoader: false,
                            data: {
                                street: street,
                                postcode: postcode,
                                city: city,
                                country: country,
                                housenumber: housenumber,
                                housenumberaddition: housenumberaddition,
                                longlat: longlat
                            }
                        }
                    ).done(
                        function (services) {

                            $("#montapacking_longitude").val(services.longitude);
                            $("#montapacking_latitude").val(services.latitude);
                            $("#montapacking_language").val(services.language);

                            $("#hasconnection").val("y");
                            if (services.hasconnection === 'false') {
                                this.hasconnection(null);
                                $("#hasconnection").val("n");
                            }

                            this.removeLoading();
                        }.bind(this)
                    );
                },

                /**
                 * Retrieve Delivery Options from Montapacking.
                 */
                getDeliveryServices: function (street, postcode, city, country, housenumber, housenumberaddition, longlat) {
                    this.setLoading();

                    $.ajax(
                        {
                            method: 'GET',
                            url: this.urlPrefix + '/montacheckout/deliveryoptions/delivery',
                            type: 'jsonp',
                            showLoader: false,
                            data: {
                                street: street,
                                postcode: postcode,
                                city: city,
                                country: country,
                                housenumber: housenumber,
                                housenumberaddition: housenumberaddition,
                                longlat: longlat
                            }
                        }
                    ).done(
                        function (services) {
                            if (services === "[]") {
                                return;
                            }

                            const objectArray = Object.values(services[0]);

                            this.deliveryServices(objectArray);

                            if (objectArray.length > 0){
                                this.preferredShipper = objectArray.find(timeframe => timeframe.options.some(option => option.is_preferred));
                                if(this.preferredShipper == null) {
                                    this.preferredShipper = objectArray[0];
                                }

                                const filteredDeliveryServicesList = objectArray.filter(timeframe => timeframe.options[0].date !== '');
                                if (filteredDeliveryServicesList.length > 0) {
                                    const distinctFilteredItems = window.monta_plugin.initDatePicker(objectArray);
                                    this.filteredDeliveryServices(filteredDeliveryServicesList.filter(timeframe =>
                                        timeframe.options[0].date === distinctFilteredItems[0].date));

                                    // set width of date picker by number of list items
                                    const width = $("ol li").length;
                                    $("#slider-content").width(width * 110);

                                    let indexOfDay = 0;
                                    if(this.preferredShipper != null && this.preferredShipper.options[0].code != "MultipleShipper_ShippingDayUnknown") {
                                        indexOfDay = distinctFilteredItems.indexOf(distinctFilteredItems.find(x=>x.date == this.preferredShipper.date));
                                    }

                                    $('#slider-content ol li:nth-child(' + (indexOfDay + 1) + ')').trigger("click");
                                }
                                this.standardDeliveryServices(objectArray.filter(timeframe =>
                                    timeframe.options[0].from === "" &&
                                    timeframe.options[0].type === 'Unknown'));
                            }

                            this.pickupServices(Object.values(services[1]));

                            this.removeLoading();
                        }.bind(this)

                    );
                },

                renderedHandler: function(){
                    window.monta_plugin.setPreferredShipper();
                },

                setPreferredShipper(){
                    var standardDeliveryServicesElement = $("#standard-delivery-services .delivery-option:not(.SameDayDelivery)");
                    var filteredDeliveryServicesElement = $("#deliveryServices-delivery-services .delivery-option:not(.SameDayDelivery)");

                    if(this.preferredShipper != null &&
                        standardDeliveryServicesElement.length == this.standardDeliveryServices().length &&
                        filteredDeliveryServicesElement.length == this.filteredDeliveryServices().length) {
                            if(this.preferredShipper.options[0].code == "MultipleShipper_ShippingDayUnknown"){
                                standardDeliveryServicesElement.find("input[value=" + this.preferredShipper.options[0].code + "]").trigger("click");

                               var sliderElement = document.getElementById('montapacking-plugin');
                               sliderElement.scrollIntoView({behavior: "smooth", block: "nearest", inline: "nearest"});
                            } else {
                                filteredDeliveryServicesElement.find("input[value=" + this.preferredShipper.options[0].code + "]").trigger("click");
                            }
                        this.preferredShipper = null;
                    }
                },

                initDatePicker: function (objectArray) {
                    const distinctFilteredItems = [];

                    //search all shipping options with delivery date, so the dates can be used for the datepicker
                    const filteredItems = objectArray.filter(timeframe => timeframe.options[0].date !== '').map(option => {
                        return {
                            "date": option.options[0].date,
                            "day": option.options[0].date_string.split(' ')[0],
                            "day_string": option.options[0].date_string.split(' ')[1].concat(' ', option.options[0].date_string.split(' ')[2]),
                            "discount_percentage" : option.options.some(x=>x.discount_percentage > 0) ? option.options.find(x=>x.discount_percentage > 0).discount_percentage : 0,
                            "discount_percentage_text" : option.options.some(x=>x.discount_percentage > 0) ? '-' + option.options.find(x=>x.discount_percentage > 0).discount_percentage + '%' : 0
                        }
                    });

                    filteredItems.sort(function(a,b) {
                        let date1 = a.date.split('-');
                        let date2 = b.date.split('-');
                        return new Date(date1[2],date1[1],date1[0]) - new Date(date2[2], date2[1], date2[0]) || b.discount_percentage - a.discount_percentage
                    })

                    // filter all duplicates
                    $.each(filteredItems, function (index, item) {
                        let alreadyAdded = false;
                        let i;
                        for (i in distinctFilteredItems) {
                            if (distinctFilteredItems[i].date === item.date) {
                                alreadyAdded = true;
                            }
                        }
                        if (!alreadyAdded) {
                            distinctFilteredItems.push(item);
                        }
                        //show max 10 days in date picker
                        if (distinctFilteredItems.length === 10) {
                            return false;
                        }
                    });

                    this.daysForSelect(distinctFilteredItems);

                    return distinctFilteredItems;
                },

                checkDiscount(){
                    return this.daysForSelect.some(x=>x.discountPercentage > 0)
                },

                setLoading() {
                    $('body').addClass('shipping-methods-loading');
                },

                removeLoading() {
                    $('body').removeClass('shipping-methods-loading');
                },

                setDeliveryOption: function (type, details, additional_info) {

                    const deliveryOption = {
                        type: type,
                        details: details,
                        additional_info: additional_info
                    };

                    const checkoutConfig = window.checkoutConfig;
                    // Do not refactor this.
                    checkoutConfig.quoteData.montapacking_montacheckout_data = JSON.stringify(deliveryOption);

                    if (type === 'delivery') {
                        window.sessionStorage.setItem('recent_delivery_shipper', JSON.stringify(deliveryOption))
                    }

                    const address = quote.shippingAddress();


                    if (address.extension_attributes === undefined) {
                        address.extension_attributes = {};
                    }

                    address.extension_attributes.montapacking_montacheckout_data = checkoutConfig.quoteData.montapacking_montacheckout_data;

                    quote.shippingAddress(address);
                    if(quote.shippingMethod() != null && quote.shippingMethod() != undefined) {
                        setShippingInformationAction();
                    }
                },

                getfilterDeliveryServicesByDate: function (date, event) {
                    $('#slider-content ol li').removeClass("selected_day");
                    const target = $(event.target).closest(".day");
                    target.addClass("selected_day");
                    target[0].scrollIntoView({behavior: "smooth", block: "nearest", inline: "nearest"});

                    window.monta_plugin.setfilterDeliveryServicesByDate(date);
                },

                setfilterDeliveryServicesByDate: function (date) {
                    const objects = this.deliveryServices;
                    const objectsFiltered = objects.filter(timeframe => timeframe.options[0].date === date.date);
                    const objectsSorted = objectsFiltered.sort((a, b) =>
                        parseInt(parseFloat(a.options[0].price_raw)) - parseInt(parseFloat(b.options[0].price_raw))
                    );
                    this.filteredDeliveryServices(objectsSorted);
                },

                moveLeft: function () {
                    const movementCalc = Math.floor(Math.min(500 * (window.innerWidth / 700), 500));
                    const slider = document.getElementById('slider');
                    slider.scrollTo({
                        top: 0,
                        left: Math.max(slider.scrollLeft - movementCalc, 0),
                        behavior: 'smooth'
                    });
                },

                moveRight: function () {
                    const movementCalc = Math.floor(Math.min(500 * (window.innerWidth / 700), 500));
                    const slider = document.getElementById('slider');

                    slider.scrollTo({
                        top: 0,
                        left: Math.min(slider.scrollLeft + movementCalc, document.getElementById('slider-content').scrollWidth),
                        behavior: 'smooth'
                    });
                },

                toggleTab: function (previousTab, currentTab, previousContent, currentContent, triggerClick = false, hideDeliverInfo = false) {
                    $(previousTab).removeClass('active');
                    $(currentTab).addClass('active');
                    $(previousContent).hide();
                    $(currentContent).fadeIn('slow');

                    if (triggerClick) {

                        if (currentTab === '.montapacking-tab-pickup') {
                            $("input.selectshipment").val("pickup");
                            $(".pickup-option:first").find("input.initialPickupRadio").trigger("click");
                            $("#date-picker").hide()
                            $("#standard-delivery-services").hide()
                            const address = JSON.parse($("#old_address").val());

                            if ($("#montapacking_addresschangedsincelastlonglatcall").val() === 'true' || $("#montapacking_latitude").val() === "" || $("#montapacking_longitude").val() === "") {
                                window.monta_plugin.getLongLat(address.street, address.postcode, address.city, address.country, address.housenumber, address.housenumberaddition, true);
                                $("#montapacking_addresschangedsincelastlonglatcall").val('false');
                            }

                        } else {

                            $("input.selectshipment").val("delivery");

                            const mostRecentShipperData = window.sessionStorage.getItem('recent_delivery_shipper');
                            if (mostRecentShipperData && mostRecentShipperData !== '') {
                                const lastSelectedShipper = JSON.parse(mostRecentShipperData).additional_info[0].code;
                                $(".delivery-option:not(.SameDayDelivery)").find("input[value=" + lastSelectedShipper + "]").trigger("click");
                            } else {
                                $('#date-picker').show()
                            }

                            $("#standard-delivery-services").show()

                            if ($(".SameDayDelivery").length) {
                                $(".havesameday").removeClass("displaynone");
                            } else {
                                $(".nothavesameday").removeClass("displaynone");
                            }
                        }
                    }

                    if (hideDeliverInfo === true) {
                        $(".delivery-information").hide();
                    }

                },

                showDeliveryOptions: function (informationTab, optionsTab) {
                    window.sessionStorage.setItem('recent_delivery_shipper', '')
                    $(informationTab).hide();
                    $(optionsTab).fadeIn('slow');
                    $("#date-picker").show();
                },

                selectShipper: function () {

                    $(".delivery-information").hide();
                    $("#date-picker").hide();
                    // set vars

                    const code = $(this).val();
                    const name = $(this).parents(".delivery-option").find(".cropped_name").text();
                    const type = $(this).parents(".delivery-option").find(".cropped_type").text();
                    let date = $(this).parents(".delivery-option").find(".cropped_date").text();
                    let date_text = $(this).parents(".delivery-option").find(".cropped_time").text();
                    const date_string = $(this).parents(".delivery-option").find(".cropped_date_text").text();

                    if (date === '01-01-1970') {
                        date = '';
                        date_text = '';
                    }

                    const time = $(this).parents(".delivery-option").find(".cropped_time").text();
                    const time_text = $(this).parents(".delivery-option").find(".cropped_time_text").text();
                    const price = $(this).parents(".delivery-option").find(".cropped_price").text();
                    const image_class = $(this).parents(".delivery-option").find(".cropped_image_class").text();
                    const image_class_replaced = $(this).parents(".delivery-option").find(".cropped_image_class_replaced").text();
                    const short_code = image_class;
                    const checked_boxes = $(this).parents(".delivery-option").find(".montapacking-container-delivery-options input[type=checkbox]:checked");
                    const option_codes = $(this).parents(".delivery-option").find(".montapacking-container-delivery-optioncodes input[type=hidden]");
                    const currency_symbol = $(this).parents(".delivery-option").find(".price_currency_symbol").text();
                    let total_price = parseFloat(price);

                      // set delivery information
                    $(".delivery-information").find(".montapacking-delivery-information-company").html(name);
                    $(".delivery-information").find(".montapacking-delivery-information-date").html(date_string);

                    if (date === '') {
                        $(".dateblock").css("display", "none");
                    } else {
                        $(".dateblock").css("display", "block");
                    }

                    if (time === '00:00-00:00' || time === '') {
                        $(".timeblock").css("display", "none");
                    } else {
                        $(".timeblock").css("display", "block");
                    }
                    $(".delivery-information").find(".montapacking-delivery-information-time").html(time_text);

                    //set image class
                    $(".delivery-information").find(".montapacking-container-logo").removeClass().addClass("montapacking-container-logo").addClass(image_class_replaced);

                    if (type === 'ShippingDay') {
                        $(".delivery-information").find(".delivered").addClass("displaynone");
                        $(".delivery-information").find(".shipped").removeClass("displaynone");
                    } else {
                        $(".delivery-information").find(".delivered").removeClass("displaynone");
                        $(".delivery-information").find(".shipped").addClass("displaynone");
                    }

                    //set delivery options

                    $("ul.montapacking-delivery-information-options").empty();

                    const options = [];

                    $(checked_boxes).each(
                        function (index, element) {
                            const text_value = $(element).parent("div").find("label").html();
                            $("ul.montapacking-delivery-information-options").append('<li>' + text_value + '</li>');

                            const raw_price = $(element).parents(".montapacking-delivery-option").find(".delivery-fee-hidden").text();
                            const option_price = parseFloat(raw_price);
                            total_price += option_price;

                            options.push($(this).val());
                        }
                    );

                    $(option_codes).each(
                        function (index, element) {
                            options.push($(element).val());
                        }
                    );


                    $('.delivery-option input[type=checkbox]:checked').not(checked_boxes).attr('checked', false);

                    $(".delivery-information").fadeIn('slow');
                    $(".delivery-option").hide();

                    total_price = total_price.toFixed(2);
                    const total_price_raw = total_price;

                    total_price = total_price.toString().replace('.', ',');

                    setTimeout(
                        function () {
                            $(".table-checkout-shipping-method").find("input[value='montapacking_montapacking']").parents(".row").find("span.price").html("&euro;" + total_price);
                        }, 250
                    );

                    $(".delivery-information").find(".montapacking-container-price").html(currency_symbol + " " + total_price);

                    const additional_info = [];
                    additional_info.push(
                        {
                            code: code,
                            name: name,
                            date: date,
                            time: time,
                            price: price,
                            total_price: total_price_raw,
                        }
                    );

                    const details = [];
                    details.push(
                        {
                            short_code: short_code,
                            options: options,
                        }
                    );

                    window.monta_plugin.setDeliveryOption('delivery', details, additional_info);
                    window.monta_plugin.deliveryFee(total_price);

                    pickupShop().parcelShopAddress(null);

                    if ($(".SameDayDelivery").length) {
                        $(".havesameday").removeClass("displaynone");
                    } else {
                        $(".nothavesameday").removeClass("displaynone");
                    }

                    return true;

                },


                selectPickUp: function () {

                    $(".pickup-information").hide();

                    // set vars
                    const code = $(this).val();
                    const shipper = $(this).parents(".pickup-option").find(".cropped_shipper").text();
                    const code_pickup = $(this).parents(".pickup-option").find(".cropped_codepickup").text();
                    const shippingoptions = $(this).parents(".pickup-option").find(".cropped_shippingoptions").text();
                    const company = $(this).parents(".pickup-option").find(".cropped_company").text();
                    const street = $(this).parents(".pickup-option").find(".cropped_street").text();
                    const housenumber = $(this).parents(".pickup-option").find(".cropped_housenumber").text();
                    const postal = $(this).parents(".pickup-option").find(".cropped_postal").text();
                    const city = $(this).parents(".pickup-option").find(".cropped_city").text();
                    const description = $(this).parents(".pickup-option").find(".cropped_description").text();
                    const country = $(this).parents(".pickup-option").find(".cropped_country").text();
                    const price = $(this).parents(".pickup-option").find(".cropped_price").text();
                    const image_class = $(this).parents(".pickup-option").find(".cropped_image_class").text();
                    const short_code = image_class;
                    const distance = $(this).parents(".pickup-option").find(".cropped_distance").text();
                    const optionsvalues = $(this).parents(".pickup-option").find(".cropped_optionswithvalue").text();
                    const openingtimes_html = $(this).parents(".pickup-option").find(".table-container .table").clone().html();
                    let total_price = parseFloat(price);

                    const n = code_pickup.includes("_packStation");

                    if (n) {
                        $("#PCPostNummer").removeClass("displaynone");

                        $(".open-business-hours").addClass("displaynone");
                        $(".block-business-hours").addClass("displaynone");
                    } else {
                        $("#PCPostNummer").val("");
                        $("#PCPostNummer").addClass("displaynone");

                        $(".open-business-hours").removeClass("displaynone");
                        $(".block-business-hours").removeClass("displaynone");
                    }

                    // set pickup information
                    $(".pickup-information").find(".montapacking-pickup-information-company").html(company);
                    $(".pickup-information").find(".montapacking-pickup-information-description-distance").html(description);
                    $(".pickup-information").find(".montapacking-pickup-information-description-street-housenumber").html(street + ' ' + housenumber);
                    $(".pickup-information").find(".montapacking-pickup-information-description-postal-city-country").html(postal + ' ' + city + ' (' + country + ')');
                    $(".pickup-information").find(".table-container .table").html(openingtimes_html);

                    // Set price currency symbol
                    const price_currency_symbol = $(this).parents(".pickup-option").find(".price_currency_symbol").text();
                    // set price
                    $(".pickup-information").find(".montapacking-container-price").html(price_currency_symbol + " " + price.replace(".", ","));

                    //set image class
                    $(".pickup-information").find(".montapacking-container-logo").removeClass().addClass("montapacking-container-logo").addClass(image_class);

                    $(".pickup-information").fadeIn('slow');

                    total_price = total_price.toFixed(2);
                    const total_price_raw = total_price;

                    total_price = total_price.toString().replace('.', '.');

                    setTimeout(
                        function () {
                            $(".table-checkout-shipping-method").find("input[value='montapacking_montapacking']").parents(".row").find("span.price").html("&euro;" + total_price);
                        }, 250
                    );

                    const additional_info = [];
                    additional_info.push(
                        {
                            code: code,
                            code_pickup: code_pickup,
                            shipper: shipper,
                            company: company,
                            street: street,
                            housenumber: housenumber,
                            postal: postal,
                            city: city,
                            description: description,
                            price: price,
                            country: country,
                            total_price: total_price_raw,
                        }
                    );

                    const details = [];
                    details.push(
                        {
                            short_code: short_code,
                            options: [],
                        }
                    );

                    window.monta_plugin.setDeliveryOption('pickup', details, additional_info);
                    window.monta_plugin.pickupFee(total_price);
                    pickupShop().parcelShopAddress(additional_info[0]);

                    return true;

                },

                showBusinessHours: function () {
                    $(this).hide();
                    $(this).parents(".montapacking-pickup-service").find('.table-container').fadeIn('slow');
                },

                closeBusinessHours: function () {
                    $(this).parent('.table-container').hide();
                    $(this).parents(".montapacking-pickup-service").find('.open-business-hours').fadeIn('slow');
                },

                showPopup: function (sHtml) {
                    $("#modular-container").css("display", "table");
                    $("#modular-background").css("display", "block");
                },

                loadPopup: function (sHtml) {

                    $("body").prepend('<div id="modular-container"/>');
                    $("body").prepend('<div id="modular-background"/>');

                    const html = `<div id="storelocator_container">
                        <div class="container">
                            <div class="bh-sl-container">
                                <div class="bh-sl-filters-container">
                                    <div class="storelocator-top-bar-container">
                                        <button type="button" data-bind="click: closePopup, i18n: 'Use selection'" class="select-item displaynone"></button>
                                        <ul id="category-filters" class="bh-sl-filters"></ul>
                                        <div class="storelocator-postcode-search-container">
                                            <p class="storelocator-postcode-search-label" data-bind="i18n: 'Postal Code'">Postcode</p>
                                            <input type="text" class="input-text storelocator-postcode-search-input" id="storelocator-postcode-search-input"/>
                                            <button type="button" class="storelocator-postcode-search-button" id="storelocator-postcode-search-button" data-bind="i18n: 'Search'"></button>
                                        </div>
                                        <button type="button" data-bind="click: closePopup, i18n: 'x'" class="select-item close-item"></button>
                                    </div>
                                </div>
                                <div id="bh-sl-map-container" class="bh-sl-map-container">
                                    <div id="bh-sl-map" class="bh-sl-map"></div>
                                    <div class="bh-sl-loc-list">
                                        <ul class="list listitemsforpopup"></ul>
                                    </div>
                               </div>
                            </div>
                        </div>
                    </div>`;

                    $("#modular-container").append(
                        '<div class="positioning">' + html + '</div>'
                    );

                    ko.applyBindings(window.monta_plugin, document.getElementById('modular-container'));

                    document.getElementById('storelocator-postcode-search-button').addEventListener('click', () => {
                        let newZip = document.getElementById('storelocator-postcode-search-input').value;
                        const address = JSON.parse($("#old_address").val());

                        let street = [];
                        street[0] = "n";
                        encodeURIComponent(JSON.stringify(street));

                        if (newZip.length > 0) {
                            this.setLoading();

                            $.ajax(
                                {
                                    method: 'GET',
                                    url: this.urlPrefix + '/montacheckout/deliveryoptions/delivery',
                                    type: 'jsonp',
                                    showLoader: false,
                                    data: {
                                        street: street,
                                        postcode: newZip,
                                        country: address.country,
                                        longlat: false
                                    }
                                }
                            ).done(
                                function (services) {
                                    this.pickupServices.removeAll();
                                    Object.values(services[1]).forEach(f => this.pickupServices.push(f));
                                    $("#montapacking_addresschangedsincelastmapload").val('true');
                                    this.setLoading();

                                    $.ajax({
                                            method: 'GET',
                                            url: this.urlPrefix + '/montacheckout/deliveryoptions/longlat',
                                            type: 'jsonp',
                                            showLoader: false,
                                            data: {
                                                street: street, postcode: newZip, country: address.country, longlat: true
                                            }
                                        }
                                    ).done(
                                        function (longlat) {
                                            $("#montapacking_latitude").val(longlat['latitude']);
                                            $("#montapacking_longitude").val(longlat['longitude']);
                                            window.monta_plugin.loadMap();
                                            document.getElementById('category-filters').style.visibility = 'hidden';
                                            window.monta_plugin.toggleTab('.montapacking-tab-pickup', '.montapacking-tab-pickup', '.pickup-services', '.pickup-services', true, true);

                                            this.removeLoading();
                                        }.bind(this));

                                    this.removeLoading();
                                }.bind(this)
                            );
                        }
                    })
                },

                closePopup: function () {

                    $("#modular-container").css("display", "none");
                    $("#modular-background").css("display", "none");
                    return false;

                },

                createSiteUrl: function(){
                    const dataUrl = window.dataUrl.split('/');
                    const site_url = dataUrl.slice(5,-2);
                    site_url.unshift('static');
                    site_url.push('Montapacking_MontaCheckout');

                    return '/' + site_url.join("/");
                },

                openStoreLocator: function () {

                    $('body').trigger('processStart');

                    require(
                        ['Handlebars',
                            'jquery',
                            'google',
                            'storeLocator'], function (Handlebars, $, google, storeLocator) {
                            window.Handlebars = Handlebars;
                            const useLocator = $('#bh-sl-map-container');
                            /* Map */
                            if (useLocator) {
                                window.monta_plugin.loadMap();
                            }
                        }
                    );
                },
                LoadFallbackList: function (useLocator) {
                    if (useLocator.data('plugin_storeLocator')) {
                        useLocator.storeLocator('mapping', {lat: 0, lng: 0});
                        document.getElementsByClassName('storelocator-postcode-search-container')[0].style.visibility = 'hidden';
                        document.getElementById('category-filters').style.visibility = 'hidden';
                        document.getElementById('bh-sl-map').style.display = 'none';
                        document.getElementsByClassName('bh-sl-loc-list')[0].style.width = '100%';
                    }
                    const html = $("#storelocator_container").html();
                    window.monta_plugin.showPopup(html);
                    $('body').trigger('processStop');

                }, loadMap: function () {
                    const useLocator = $('#bh-sl-map-container');
                    const markers = [];
                    const site_url = window.monta_plugin.createSiteUrl();
                    $(".montapacking-pickup-service.pickup-option").each(
                        function (index) {
                            const openingtimes = $(this).find(".table-container .table").html();

                            markers.push(
                                {
                                    'id': $(this).attr("data-markerid"),
                                    'listid': $(this).attr("data-markerid"),
                                    'category': $(this).find("span.cropped_shipper").text(),
                                    'code': $(this).find("span.cropped_code").text(),
                                    'shippingOptions': 1,
                                    'name': $(this).find("span.cropped_company").text(),
                                    'lat': $(this).find("span.cropped_lat").text(),
                                    'lng': $(this).find("span.cropped_lng").text(),
                                    'distance': ($(this).find("span.cropped_distance").text() / 1000),
                                    'street': $(this).find("span.cropped_street").text(),
                                    'houseNumber': $(this).find("span.cropped_housenumber").text(),
                                    'city': $(this).find("span.cropped_city").text(),
                                    'postal': $(this).find("span.cropped_postal").text(),
                                    'country': $(this).find("span.cropped_country").text(),
                                    'description': $(this).find("span.cropped_description").text(),
                                    'image': site_url + '/images/' + $(this).find("span.cropped_image_class").text() + '.png',
                                    'price': $(this).find("span.cropped_price").text(),
                                    'priceformatted': $(this).find(".price_currency_symbol").text() + " " + $(this).find("span.cropped_price").text().replace(".", ","),
                                    'openingtimes': openingtimes,
                                    'raw': 1,
                                }
                            );

                            if ($('.cat-' + $(this).find("span.cropped_shipper").text() + '').length === 0) {
                                const html = '<li class="cat-' + $(this).find("span.cropped_shipper").text() + '"><label><input checked="checked" type="checkbox" name="category" value="' + $(this).find("span.cropped_shipper").text() + '"> ' + $(this).find("span.cropped_description_storelocator").text() + '</label></li>';
                                $('#category-filters').append(html);
                            }
                        }
                    );

                    const config = {
                        'debug': false,
                        'pagination': false,
                        'infowindowTemplatePath': site_url + '/template/checkout/storelocator/infowindow-description.html',
                        'listTemplatePath': site_url + '/template/checkout/storelocator/location-list-description.html',
                        'distanceAlert': -1,
                        'dataType': "json",
                        'dataRaw': JSON.stringify(markers, null, 2),
                        'slideMap': false,
                        'inlineDirections': false,
                        'originMarker': true,
                        'dragSearch': false,
                        'defaultLoc': true,
                        'defaultLat': $("#montapacking_latitude").val(),
                        'defaultLng': $("#montapacking_longitude").val(),
                        'lengthUnit': 'km',
                        'exclusiveFiltering': true,
                        'taxonomyFilters': {
                            'category': 'category-filters',
                        },
                        catMarkers: {
                            'PAK': [site_url + '/images/PostNL.png', 32, 32],
                            'DHLservicepunt': [site_url + '/images/DHL.png', 32, 32],
                            'DPDparcelstore': [site_url + '/images/DPD.png', 32, 32],
                            'AFH': [site_url + '/images/AFH.png', 32, 32],
                            'DHLFYPickupPoint': [site_url + '/images/DHLFYPickupPoint.png', 32, 32],
                            'DHLParcelConnectPickupPoint': [site_url + '/images/DHLParcelConnectPickupPoint.png', 32, 32],
                            'DHLservicepuntGroot': [site_url + '/images/DHLservicepuntGroot.png', 32, 32],
                            'BudbeePickupPoint': [site_url + '/images/BudbeePickupPoint.png', 32, 32],
                            'GLSPickupPoint': [site_url + '/images/GLSPickupPoint.png', 32, 32],
                            'UPSAP': [site_url + '/images/UPSAP.png', 32, 32]
                        },
                        callbackMarkerClick: function (marker, markerId, $selectedLocation, location) {
                            $(".bh-sl-container .bh-sl-filters-container .select-item").css("display", "block");
                            $(".pickup-option[data-markerid=" + location.listid + "]").find(".initialPickupRadio").trigger("click");
                        },
                        callbackListClick: function (markerId, selectedMarker, location) {
                            const selected_input = location.code;

                            $(".bh-sl-container .bh-sl-filters-container .select-item").css("display", "block");
                            $(".pickup-option[data-markerid=" + location.listid + "]").find(".initialPickupRadio").trigger("click");
                        },
                        callbackFilters: function () {
                            const html = $("#storelocator_container").html();
                            window.monta_plugin.showPopup(html);
                            $('body').trigger('processStop');
                        },
                        callbackFormVals: function () {
                            const html = $("#storelocator_container").html();
                            window.monta_plugin.showPopup(html);
                            $('body').trigger('processStop');
                        },
                        callbackNotify: function (error) {
                            window.monta_plugin.LoadFallbackList(useLocator);
                        }
                    };
                    if (!useLocator.data('plugin_storeLocator')) {
                        useLocator.storeLocator(config);
                    } else if ($("#montapacking_addresschangedsincelastmapload").val() === 'true') {
                        useLocator.storeLocator('destroy');
                        useLocator.storeLocator(config);
                        $('#bh-sl-map-container').show();
                    } else {
                        var html = $("#storelocator_container").html();
                        window.monta_plugin.showPopup(html);
                        $('body').trigger('processStop');
                    }
                    $("#montapacking_addresschangedsincelastmapload").val('false');
                },
            }
        );
    }
);
