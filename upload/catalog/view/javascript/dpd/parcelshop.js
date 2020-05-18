var parcelshopController = {

    mapWidth: null,

    init: function () {
        window.markers = [];
        window.infowindows = [];

        parcelshopController.monitorParcelshopSelection();

        $.ajax(
            {
                method: 'GET',
                dataType: 'json',
                url: 'index.php?route=extension/shipping/dpdbenelux/translations',
                success: function (result) {
                    window.dpd_translations = result;
                }
            }
        );

        $('body').on('change', 'input[name="shipping_method"]', parcelshopController.shippingMethodChanged);

        $('body').on(
            'click', '#parcelshop-selection-map', function (e) {
                e.preventDefault();
                parcelshopController.selectParcelshop(true);
            }
        );

    },

    monitorParcelshopSelection: function () {
        // Initial radio button selection doesn't trigger an event when Parcelshop is the first shipping method
        // Doing it this way works
        setTimeout(
            function () {
                if ($('input[name="shipping_method"]').is(":visible")) {

                    if ($('input[name="shipping_method"]:checked').val() === 'dpdbenelux.parcelshop' && !$('#parcelshop-container').length) {
                        $('input[name="shipping_method"][value="dpdbenelux.parcelshop"]').trigger('change');
                    }
                }
                parcelshopController.monitorParcelshopSelection();
            }, 500
        );
    },

    shippingMethodChanged: function () {
        var selectedShippingMethod = $('input[name="shipping_method"]:checked').val();

        if (selectedShippingMethod === 'dpdbenelux.parcelshop') {

            $(this).closest('.radio').append('<div id="parcelshop-container"><a href="#" id="parcelshop-selection-map">' + window.dpd_translations.dpd_click_to_load + window.dpd_translations.dpd_on_map + '</a>&nbsp;&nbsp;&nbsp;&nbsp;</a><div id="dpd-maps-container"></div></div>');
            parcelshopController.showMapsLinkIfApplicable();

            // Block the next button till a valid parcelshop is selected
            $('#button-shipping-method').attr('disabled', true);
        } else {
            $('#parcelshop-container').remove();
            $('#button-shipping-method').attr('disabled', false);
        }
    },

    selectParcelshop: function (fromMap) {
        // Block the next button till a valid parcelshop is selected
        $('#button-shipping-method').attr('disabled', true);

        // Remove the old chosen option
        $('#dpd-maps-container').html('');

        $("[id^='selected_parcelshop_']").remove();

        postcode = $('#input-shipping-postcode').val();
        country = $('#input-shipping-country option:selected').text();
        countryId = $('#input-shipping-country option:selected').val();


        $.ajax(
            {
                method: 'POST',
                dataType: 'json',
                url: 'index.php?route=extension/shipping/dpdbenelux/parcelshop',
                data: {
                    postcode: postcode,
                    countryId: countryId,
                    country: country
                },
                error: function (result) {
                    console.debug(JSON.stringify(result));
                },
                success: function (result) {
                    if (!result.success) {
                        $('#dpd-maps-container').html("<div>" + result.error_message + "</div>");
                        return;
                    }
                    parcelshopController.parcelshopsRetrieved(result, fromMap);
                }
            }
        )
    },

    showMapsLinkIfApplicable: function () {
        // stylesheet media query hides map and shows list if media width < 768px
        if (window.innerWidth >= 768) {
            $('#parcelshop-selection-map').show();
        } else {
            $('#parcelshop-selection-map').hide();
        }
    },

    parcelshopsRetrieved: function (result, useMap) {

        var parcelShops = result.parcelshops;
        var mapHeight = result.mapHeight;
        var mapWidth = result.mapWidth;
        var mapWidthType = result.mapWidthType;
        var longitude = result.longitude;
        var latitude = result.latitude;

        parcelshopController.initListAndModals(mapHeight, mapWidth, mapWidthType, parcelShops);
        if (useMap) {
            $('#parcelshop-selection-map').hide();

            parcelshopController.initGoogleMap(mapHeight, mapWidth, mapWidthType, latitude, longitude);
            parcelshopController.setParcelshopsOnMap(parcelShops);
        } else {
            $('#parcelshop-selection-map').show();
            $('#googlemap_shops').show();
        }

        $("#parcelshops").on(
            "click", ".ParcelShops", function () {
                parcelshopController.parcelshopSelected(this.id, parcelShops);
            }
        );
    },

    parcelshopSelected: function (id, parcelShops) {
        $('#parcelshop-container').append('<input id="selected_parcelshop_id" name="selected_parcelshop_id" type="hidden">');
        $("#selected_parcelshop_id").val(id);

        for(var i = 0; i < parcelShops.length; i++) {
            var shop = parcelShops[i];
            if(id === shop.parcelShopId) {

                //create hidden inputs for the chosen parcelshop
                $('#parcelshop-container').append('<input id="selected_parcelshop_company" name="selected_parcelshop_company" type="hidden">');
                $('#parcelshop-container').append('<input id="selected_parcelshop_street" name="selected_parcelshop_street" type="hidden">');
                $('#parcelshop-container').append('<input id="selected_parcelshop_zipcode" name="selected_parcelshop_zipcode" type="hidden">');
                $('#parcelshop-container').append('<input id="selected_parcelshop_city" name="selected_parcelshop_city" type="hidden">');
                $('#parcelshop-container').append('<input id="selected_parcelshop_country" name="selected_parcelshop_country" type="hidden">');

                $('#selected_parcelshop_company').val(shop.company);
                $('#selected_parcelshop_street').val(shop.street + ' ' + shop.houseNo);
                $('#selected_parcelshop_zipcode').val(shop.zipCode);
                $('#selected_parcelshop_city').val(shop.city);
                $('#selected_parcelshop_country').val(shop.isoAlpha2);
                break;
            }
        }

        $.ajax(
            {
                url: 'index.php?route=extension/shipping/dpdbenelux/save-parcelshop',
                type: 'post',
                data: $("[id^='selected_parcelshop_']"),
                dataType: 'json',
                success: function (json) {
                    // Don't need to do anything, OC's next step already takes care of taking us to the next step
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                }
            }
        );

        $('#dpd-maps-container').remove();

        $('#parcelshop-selection-map').remove();

        $('#parcelshop-container').append('<div id="dpd-maps-container"></div><a href="#" id="parcelshop-selection-map">' + window.dpd_translations.dpd_click_to_change + window.dpd_translations.dpd_on_map + '</a>&nbsp;&nbsp;&nbsp;&nbsp;</a>');
        parcelshopController.showMapsLinkIfApplicable();

        $('#dpd-maps-container').html(
            '<div class="dpd-parcelshop-information"><p><strong>' + $('#selected_parcelshop_company').val() + '</strong><br />' +
            $('#selected_parcelshop_street').val() +'<br />' +
            $('#selected_parcelshop_zipcode').val()  +' ' + $('#selected_parcelshop_city').val()  +'<br />' +
            '</p></div>'
        );

        // Parcelshop selected, allow user to continue
        $('#button-shipping-method').attr('disabled', false);
    },

    initListAndModals: function (mapHeight, mapWidth, mapWidthType, parcelShops) {

        if ($('#parcelshops').length > 0) {
            return;
        }

        $('#dpd-maps-container').append('<div id="parcelshops" ></div>');

        var sizeIndicator = mapWidthType == 'percentage' ? '%' : 'px';

        $('#parcelshops').append('<div id="googlemaps" class="col-sm-12" style="width: ' + mapWidth + sizeIndicator + '; height: ' + mapHeight + '"></div>');
        $('#parcelshops').append('<ul id="googlemap_shops"></ul>');

        parcelShops.map(
            function (shop) {
                var content = "<img src='/img/pickup.png'/><strong class='modal-title'>" + shop.company + "</strong><br/>" + shop.street + " " + shop.houseNo + "<br/>" + shop.zipCode + " " + shop.city + "<hr>";
                var openingshours = "";

                for (var i = 0; i < shop.openingHours.length; i++) {
                    var hours = shop.openingHours[i];
                    var openingshours = openingshours + "<div class='modal-week-row'><strong class='modal-day'>" + window.dpd_translations[hours.weekday.toLowerCase()] + "</strong>" + " " + "<p>" + hours.openMorning + " - " + hours.closeMorning + "  " + hours.openAfternoon + " - " + hours.closeAfternoon + "</p></div>";
                }

                $('#parcelshops').append(
                    '<div class="parcel_modal" id="info_' + shop.parcelShopId + '">' +
                    '<img src="/catalog/view/theme/default/image/pickup.png">' +
                    '<a class="go-back">X</a>' +
                    '<strong class="modal-title">' + shop.company + '</strong><br>' +
                    shop.street + ' ' + shop.houseNo + '<br>' + shop.zipCode + ' ' + shop.city +
                    '<strong class="modal-link"><a id="' + shop.parcelShopId + '" class="ParcelShops">' + window.dpd_translations.dpd_ship_here + '</a></strong>' +
                    '<hr>' +  openingshours +
                    '</div>'
                );

                $('#parcelshops').on(
                    'click', '.go-back', function () {
                        $('#googlemap_shops').show();
                        $('.parcel_modal').hide();
                    }
                );

                var sidebar_item = $("<li><div class='sidebar_single'><strong class='company'>" + shop.company + "</strong><br/><span class='address'>" + shop.street + " " + shop.houseNo + "</span><br/><span class='address'>" + shop.zipCode + " " + shop.city + "</span><br/><strong class='modal-link'><a id='more_info_" + shop.parcelShopId + "' class='more-information'>" + window.dpd_translations.dpd_more_information + "</a></strong></div></li>");

                sidebar_item.on(
                    'click', '.more-information', function () {
                        $('#googlemap_shops').hide();
                        $('#info_' + shop.parcelShopId).show();
                    }
                );


                $('#googlemap_shops').append(sidebar_item);
            }
        );
    },

    setParcelshopsOnMap: function (parcelshops) {

        var markerBounds = new google.maps.LatLngBounds();

        parcelshops.map(
            function (shop) {
                var marker_image = new google.maps.MarkerImage('/catalog/view/theme/default/image/pickup.png', new google.maps.Size(57, 62), new google.maps.Point(0, 0), new google.maps.Point(0, 31));
                var marker = new google.maps.Marker(
                    {
                        position: new google.maps.LatLng(parseFloat(shop.latitude),parseFloat(shop.longitude)),
                        icon: marker_image,
                        map: window.map
                    }
                );

                var infowindow = new google.maps.InfoWindow();


                var content = "<img src='/catalog/view/theme/default/image/pickup.png'/><strong class='modal-title'>"+shop.company+"</strong><br/>"+ shop.street + " " + shop.houseNo + "<br/>" + shop.zipCode + " " + shop.city ;
                var openingshours = "";

                for (var i = 0; i < shop.openingHours.length; i++) {
                    var hours = shop.openingHours[i];
                    var openingshours = openingshours + "<div class='modal-week-row'><strong class='modal-day'>" + window.dpd_translations[hours.weekday.toLowerCase()] + "</strong>" + " "+ "<p>"+ hours.openMorning + " - " + hours.closeMorning + "  " + hours.openAfternoon + " - " + hours.closeAfternoon +"</p></div>";
                }

                infowindow.setContent("<div class='info-modal-content'>" + content + "<strong class='modal-link'><a id='"+shop.parcelShopId+"' class='ParcelShops'>" + window.dpd_translations.dpd_ship_here + "</a></strong> " + openingshours + "</div>");
                window.infowindows.push(infowindow);

                google.maps.event.addListener(
                    marker, 'click', (function (marker) {
                        return function () {
                            parcelshopController.closeInfoWindows();
                            infowindow.open(window.map, marker);
                        }
                    })(marker)
                );

                markerBounds.extend(new google.maps.LatLng(shop.latitude, shop.longitude));

                window.markers.push(marker);

            }
        );
        window.map.fitBounds(markerBounds);
    },

    closeInfoWindows: function () {
        for (var i = 0; i < window.infowindows.length; i++) {
            window.infowindows[i].close();
        }
    },

    initGoogleMap: function (mapHeight, mapWidth, mapWidthType, latitude, longitude) {
        var styledMapType = new google.maps.StyledMapType(
            [
                {
                    "elementType": "geometry",
                    "stylers": [
                        {
                            "color": "#f5f5f5"
                    }
                    ]
                },
                {
                    "elementType": "labels.icon",
                    "stylers": [
                        {
                            "visibility": "off"
                    }
                    ]
                },
                {
                    "elementType": "labels.text.fill",
                    "stylers": [
                        {
                            "color": "#616161"
                    }
                    ]
                },
                {
                    "elementType": "labels.text.stroke",
                    "stylers": [
                        {
                            "color": "#f5f5f5"
                    }
                    ]
                },
                {
                    "featureType": "administrative.land_parcel",
                    "elementType": "labels.text.fill",
                    "stylers": [
                        {
                            "color": "#bdbdbd"
                    }
                    ]
                },
                {
                    "featureType": "poi",
                    "elementType": "geometry",
                    "stylers": [
                        {
                            "color": "#eeeeee"
                    }
                    ]
                },
                {
                    "featureType": "poi",
                    "elementType": "labels.text.fill",
                    "stylers": [
                        {
                            "color": "#757575"
                    }
                    ]
                },
                {
                    "featureType": "poi.park",
                    "elementType": "geometry",
                    "stylers": [
                        {
                            "color": "#e5e5e5"
                    }
                    ]
                },
                {
                    "featureType": "poi.park",
                    "elementType": "labels.text.fill",
                    "stylers": [
                        {
                            "color": "#9e9e9e"
                    }
                    ]
                },
                {
                    "featureType": "road",
                    "elementType": "geometry",
                    "stylers": [
                        {
                            "color": "#ffffff"
                    }
                    ]
                },
                {
                    "featureType": "road.arterial",
                    "elementType": "labels.text.fill",
                    "stylers": [
                        {
                            "color": "#757575"
                    }
                    ]
                },
                {
                    "featureType": "road.highway",
                    "elementType": "geometry",
                    "stylers": [
                        {
                            "color": "#dadada"
                    }
                    ]
                },
                {
                    "featureType": "road.highway",
                    "elementType": "labels.text.fill",
                    "stylers": [
                        {
                            "color": "#616161"
                    }
                    ]
                },
                {
                    "featureType": "road.local",
                    "elementType": "labels.text.fill",
                    "stylers": [
                        {
                            "color": "#9e9e9e"
                    }
                    ]
                },
                {
                    "featureType": "transit.line",
                    "elementType": "geometry",
                    "stylers": [
                        {
                            "color": "#e5e5e5"
                    }
                    ]
                },
                {
                    "featureType": "transit.station",
                    "elementType": "geometry",
                    "stylers": [
                        {
                            "color": "#eeeeee"
                    }
                    ]
                },
                {
                    "featureType": "water",
                    "elementType": "geometry",
                    "stylers": [
                        {
                            "color": "#d2e4f3"
                    }
                    ]
                },
                {
                    "featureType": "water",
                    "elementType": "labels.text.fill",
                    "stylers": [
                        {
                            "color": "#9e9e9e"
                    }
                    ]
                }
            ],
            {name: 'Styled Map'}
        );

        var mapsCanvas = $('#googlemaps');

        mapsCanvas.height(mapHeight);
        if(mapWidthType == 'percentage') {
            mapsCanvas.width(mapWidth + '%');
        } else {
            mapsCanvas.width(mapWidth + 'px');
        }


        // Create a map object, and include the MapTypeId to add
        // to the map type control.
        window.map = new google.maps.Map(
            mapsCanvas.get(0), {
                zoom: 11,
                center: {lat: latitude, lng: longitude},
                mapTypeControlOptions: {
                    mapTypeIds: ['styled_map']
                }
            }
        );

        //Associate the styled map with the MapTypeId and set it to display.
        window.map.mapTypes.set('styled_map', styledMapType);
        window.map.setMapTypeId('styled_map');
    },

};

$('document').ready(parcelshopController.init);
