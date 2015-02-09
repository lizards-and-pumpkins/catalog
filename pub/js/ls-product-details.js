jQuery(function ($) {

    var ProductDetails = {
        cloudZoomHeight: 389,
        cloudZoomWidth: 600,

        initiate: function () {
            if (!$('div#productView').length) return;
            ProductDetails.fixIOS5Bug();
            ProductDetails.initiateTabs();
            ProductDetails.initiateCloudZoom();
            ProductDetails.initiateSocialMediaItems();
            ProductDetails.observeSizeDropDown();
            ProductDetails.initiateFullScreenImageViewer();
            ProductDetails.initiate360View();
//            ProductDetails.initiateOverlays();
            ProductDetails.initiateYouTubePlayer();
        },

        initiateTabs: function()
        {
            $('ul.tabs').each(function() {
                var links = $(this).find('a'),
                    active = $(links.filter('[href="' + location.hash + '"]')[0] || links[0]).addClass('active'),
                    content = $(active[0].hash);

                links.not(active).each(function () {
                    $(this.hash).hide();
                });

                $(this).on('click', 'a', function(e){
                    active.removeClass('active');
                    content.hide();

                    active = $(this);
                    content = $(this.hash);

                    active.addClass('active');
                    content.show();

                    e.preventDefault();
                });
            });
        },

        initiateOverlays: function()
        {
            $('.product-shop .productAlert').bind('click', function () {
                /* Show popup */
                new ModalBox().show(jQuery('#alertLayer').html());

                /* Apply uniform for drop-down */
                $('.modal-popup .alertPopUp select').uniform({selectAutoWidth: false});

                /* Bind action to button */
                $('.alertPopUp button').on('click', function () {
                    location.href = jQuery(this).parents('.alertPopUp').find('select').val();
                });
            });
            $('.availability .info').bind('click', function () {
                new ModalBox().show(Mage.baseUrl + 'common/index/index/?identifier=versandinfo');
            });
        },
        /**
         * Does a check if there are product images and if the first image
         * has the right dimensions, if so enables the Cloud Zoom functionality
         */
        initiateCloudZoom: function () {
            if ($('a.cloud-zoom').length || $('div#productView div.product-essential div.product-img-box div.more-views a').length) {
                var zoomImagePath = $('a#main-image').attr('href');
                zoomImagePath = ($.browser.webkit || $.browser.msie) ? zoomImagePath + '?' + new Date().getTime() : zoomImagePath;
                var zoomImageWidth, zoomImageHeight;
                // Creates a temporary image from the main one in order to read the dimensions of that one
                $('<img/>').attr('src', zoomImagePath).load(function () {
                    zoomImageWidth = this.width;
                    zoomImageHeight = this.height;
                    if (zoomImageWidth >= ProductDetails.cloudZoomWidth || zoomImageHeight >= ProductDetails.cloudZoomHeight) {
                        ProductDetails.enableCloudZoom();
                    } else {
                        $('a#main-image').removeAttr('href');
                        $('div#productView div.product-essential div.product-img-box div.more-views a.cloud-zoom-gallery').each(ProductDetails.cloudZoomGaleryItemAlternativeClickHandlerInitiator);
                    }
                });
            }
        },
        enableCloudZoom: function () {
            var cloudZoomParameters = {position: 'inside'};
            if (APPLICATION_MANAGER_SETTINGS.CURRENT_SHOP !== 'cy') {
                cloudZoomParameters = {
                    zoomWidth: ProductDetails.cloudZoomWidth,
                    zoomHeight: ProductDetails.cloudZoomHeight,
                    position: 'right'
                };
                var cloudZoomSuplementaryParameters = $.browser.msie ? {
                    adjustX: 10,
                    adjustY: 5,
                    appendTo: 'div#productView'
                } : {adjustX: 5};
                $.extend(cloudZoomParameters, cloudZoomSuplementaryParameters);
            }
            $('a#main-image').css('cursor', 'move');
            $('a.cloud-zoom, a.cloud-zoom-gallery').CloudZoom(cloudZoomParameters);
            $('.cloud-zoom-gallery').bind('pre-gallery-switch', ProductDetails.resetImageContainer);
        },
        /**
         * Alternative image switcher function, if the medias are to small
         */
        cloudZoomGaleryItemAlternativeClickHandlerInitiator: function () {
            $(this).data('href', $(this).attr('href'));
            $(this).removeAttr('href');
            $('a#main-image').css({height: ProductDetails.mediaHeight});
            var mainImage = $('a#main-image img');
            $(this).click(function () {
                mainImage.css({opacity: 0});
                var imageToLoad = ($.browser.webkit || $.browser.msie) ? $(this).data('href') + '?' + new Date().getTime() : $(this).data('href');
                mainImage.attr('src', imageToLoad);
                mainImage.bind('load', ProductDetails.cloudZoomGaleryItemsAlternativeClickHandlerInitiatorImageLoadeHandler);
                ProductDetails.resetImageContainer();
            });
        },
        cloudZoomGaleryItemsAlternativeClickHandlerInitiatorImageLoadeHandler: function (event) {
            var mainImage = $('a#main-image img');
            mainImage.unbind('load', ProductDetails.cloudZoomGaleryItemsAlternativeClickHandlerInitiatorImageLoadeHandler);
            var imageHeight = mainImage.height();
            var imageMarginTop = (ProductDetails.mediaHeight - imageHeight) * .5;
            mainImage.css({marginTop: imageMarginTop});
            mainImage.stop(true, false).animate({opacity: 1});
        },
        initiateFullScreenImageViewer: function () {
            if (typeof(slideshowSwf) == 'undefined') return;
            $('div#productView div.slideshow').flash({
                swf: slideshowSwf,
                allowfullscreen: true,
                flashvars: {
                    data: JSON.stringify(slideshowData)
                }
            });
        },
        initiateYouTubePlayer: function () {
            var holder = $('div#productView div.youTubeHolder');
            $('div#productView div.product-essential div.product-img-box div.more-views a.youtube').click(function () {
                ProductDetails.hideSwfObjects();
                var youTubeOpts = {
                    swf: 'http://www.youtube.com/v/' + youTubeLink + '?version=3&&autohide=1&autoplay=1&egm=1&hd=1&modestbranding=1&rel=0&showsearch=0',
                    id: 'player',
                    width: mediaWidth,
                    height: mediaHeight,
                    allowfullscreen: true,
                    flashvars: {
                        autohide: true
                    }
                };
                holder.flash(youTubeOpts);
                holder.show();
            });
        },
        initiate360View: function () {
            $('div#productView div.product-essential div.product-img-box div.more-views a.panorama').click(function () {
                ProductDetails.hideSwfObjects();
                var panorama = jQuery('div#productView div.panorama');
                panorama.flash({
                    swf: panorama_swf,
                    width: mediaWidth,
                    height: mediaHeight
                });
                panorama.show();
            });
        },
        hideSwfObjects: function () {
            $('div#productView div.panorama, div#productView div.youTubeHolder, div#productView div.slideshow, div#productView div.main-image-area-wrapper div#wrap div.mousetrap').hide();
            $('div#productView div.panorama, div#productView div.youTubeHolder').find('object, embed').detach();
        },
        resetImageContainer: function () {
            ProductDetails.hideSwfObjects();
            $('div#productView div.main-image-area-wrapper div#wrap div.mousetrap, div#productView div.slideshow').show();
        },
        observeSizeDropDown: function () {
            $('div#productView select').not('#qty').eq(0).bind('change', function (event) {
                var selected_item = $(event.target).find(':selected'),
                    selected_item_sold_out = !$(event.target).length ||
                        typeof selected_item.attr('disabled') != 'undefined' ||
                        $(event.target).prop('selectedIndex') == 0,
                    qty_box = $('.qty-box'),
                    buttonInstance = $('#productView').find('button[type=submit]'),
                    selectedItemStockAvailable = selected_item.attr('stock');

                $('.product-shop span.availability').hide();
                if (selectedItemStockAvailable && selectedItemStockAvailable.match(/[0-9]+/)) {
                    $('.product-shop span.selectedSize').html(selected_item.text());
                    $('.product-shop span.availability' + (selected_item_sold_out ? '.false' : '.true')).show();
                    var qty = qty_box.find('select');
                    qty.find('option').remove();
                    for (var i = 0; i < selectedItemStockAvailable; i++) {
                        qty.append(jQuery('<option>' + (i + 1) + '</option>'));
                    }
                    jQuery.uniform.update('.qty-box select');
                } else {
                    $('.product-shop span.availability.choose').show();
                    $('.product-shop .selectedSize').html('');
                }
                selected_item_sold_out ? qty_box.hide() : qty_box.show();
                selected_item_sold_out ? buttonInstance.attr('disabled', 'disabled') : buttonInstance.removeAttr('disabled');
            }).trigger('change');
        },
        initiateSocialMediaItems: function () {
            var enableFacebookButton = function () {
                (function (d, s, id) {
                    var language = {
                        'de': 'de_DE',
                        'en': 'en_US',
                        'fr': 'fr_FR'
                    };
                    var js, fjs = d.getElementsByTagName(s)[0];
                    if (d.getElementById(id)) return;
                    js = d.createElement(s);
                    js.id = id;
                    js.src = "//connect.facebook.net/" + language['de_DE'] + "/all.js#xfbml=1&status=0";
                    fjs.parentNode.insertBefore(js, fjs);
                }(document, 'script', 'facebook-jssdk'));
            };
            var enableTwitterButton = function () {
                !function (d, s, id) {
                    var js, fjs = d.getElementsByTagName(s)[0];
                    if (!d.getElementById(id)) {
                        js = d.createElement(s);
                        js.id = id;
                        js.src = "//platform.twitter.com/widgets.js";
                        fjs.parentNode.insertBefore(js, fjs);
                    }
                }(document, "script", "twitter-wjs");
            };
            var enableGooglePlusButton = function () {
                (function (d, s, id) {
                    var js, fjs = d.getElementsByTagName(s)[0];
                    if (!d.getElementById(id)) {
                        js = d.createElement(s);
                        js.id = id;
                        js.src = "//apis.google.com/js/plusone.js";
                        fjs.parentNode.insertBefore(js, fjs);
                    }
                }(document, 'script', 'google-sdk'));
            };
            enableFacebookButton.apply(this);
            enableTwitterButton.apply(this);
            enableGooglePlusButton.apply(this);

        },
        /**
         * iOS5 has a problem disabling <select> elements. Hence such elements are removed if such device is detected.
         */
        fixIOS5Bug: function () {
            if (navigator.userAgent.match(/\(iP[^)]+OS\s5/)) {
                jQuery('#switcher-container').find('option:disabled').remove();
            }
        }
    };
    ProductDetails.initiate();
});

jQuery(document).ready(function () {
    adjustDetailsToWidth();
    jQuery(window).bind('resize orientationchange', adjustDetailsToWidth);
});

function adjustDetailsToWidth() {
    var currentWidth = jQuery(window).width(),
    /* Maybe it makes sense to initialize variables on load only ? */
        phoneTitlePlaceholder = jQuery('#phoneTitlePlaceholder'),
        productTitle = jQuery('.product-title-info h1'),
        brandLogo = jQuery('.product-title img'),
        socialIcons = jQuery('.socialSharing'),
        productTopContainer = jQuery('.product-shop > .top'),
        productControls = jQuery('.product-controls'),
        price = jQuery('.price-information'),
        productMainInfo = jQuery('.product-main-info'),
        similarProductsLink = jQuery('.similarProducts'),
        articleInformations = jQuery('.articleInformations'),
        productShopColumn = jQuery('.product-shop'),
        productImageColumn = jQuery('.product-img-box'),
        essentialsContainer = jQuery('.product-essential');

    /* Phone only */
    if (currentWidth < tabletWidth) {

        /* Create product title placeholder if not exists */
        if (!phoneTitlePlaceholder.length) {
            phoneTitlePlaceholder = jQuery('<div/>', {
                'id': 'phoneTitlePlaceholder'
            });
            essentialsContainer.prepend(phoneTitlePlaceholder);
        }

        /* Move product title if required */
        if (!phoneTitlePlaceholder.has(productTitle).length) {
            phoneTitlePlaceholder.append(productTitle);
        }

        /* Move brand logo if required */
        if (!phoneTitlePlaceholder.has(brandLogo).length) {
            phoneTitlePlaceholder.append(brandLogo);
        }

        /* Move social icons if required */
        if (!productControls.has(socialIcons).length) {
            socialIcons.insertBefore(jQuery('.otherLinks'));
        }

        /* Move price if required */
        if (!productTopContainer.has(price).length) {
            productTopContainer.prepend(price);
        }

        /* Move similar product link if required */
        if (!productControls.has(similarProductsLink).length) {
            similarProductsLink.insertBefore(socialIcons);
        }

        /* Move SKU if required */
        if (!productControls.has(articleInformations).length) {
            articleInformations.insertBefore(socialIcons);
        }

        /* Create images list if required */
        var imagesList = jQuery('#images-carousel');
        if (!imagesList.length) {
            imagesList = jQuery('<ul/>', {'id': 'images-carousel', 'class': 'slides'});
            jQuery('.more-views > ul > li > a').each(function () {
                var src = jQuery(this).attr('href');
                if (src && src.match(/^http/)) {
                    imagesList.append(jQuery('<li/>').append(jQuery('<img/>', {'src': src})));
                }
            });
            productImageColumn.prepend(jQuery('<div/>', {'class': 'flexslider'}).append(imagesList));
            jQuery('.flexslider').flexslider({'slideshow': false, 'animationLoop': false});
        }

        /* Hide "send" part of FB buttons block if not yet hidden */
        fbEnsureInit(processFbButton);
    } else {
        var originalTitleContainer = jQuery('.product-title-info');

        /* Move product title back if required */
        if (!productTitle.length) {
            originalTitleContainer.prepend(phoneTitlePlaceholder.find('h1'));
        }

        /* Move brand logo back if required */
        if (!brandLogo.length) {
            originalTitleContainer.parent().prepend(phoneTitlePlaceholder.find('img'));
        }

        /* Move social icons back if required */
        if (!productTopContainer.has(socialIcons).length) {
            productTopContainer.append(socialIcons);
        }

        /* Move price back if required */
        if (!productMainInfo.has(price).length) {
            price.insertAfter(productMainInfo.find('.main-information'));
        }

        /* Move similar product link back if required */
        if (!originalTitleContainer.has(similarProductsLink).length) {
            originalTitleContainer.append(similarProductsLink);
        }

        /* Move SKU back if required */
        if (!originalTitleContainer.parent().has(articleInformations).length) {
            articleInformations.insertBefore(originalTitleContainer.parent());
        }

        /* Revert "send" part of FB buttons block if not yet recovered */
        fbEnsureInit(processFbButton);
    }

    /* Reset .product-shop price */
    productShopColumn.width('');

    /* Tablet only */
    if (currentWidth < siteFullWidth && currentWidth >= tabletWidth) {

        /* Constantly update .product-shop price */
        productShopColumn.width(essentialsContainer.outerWidth() - productImageColumn.outerWidth() - 30);

        /* Move price if required */
        if (!productTopContainer.has(price).length) {
            productTopContainer.append(price);
        }

        /* Move SKU if required */
        if (!productControls.has(articleInformations).length) {
            articleInformations.insertAfter(socialIcons);
        }

    } else if (currentWidth >= siteFullWidth) {

        /* Move price back if required */
        if (!productMainInfo.has(price).length) {
            price.insertAfter(productMainInfo.find('.main-information'));
        }

        /* Move SKU back if required */
        if (!originalTitleContainer.parent().has(articleInformations).length) {
            articleInformations.insertBefore(originalTitleContainer.parent());
        }

    }
}

/**
 * Wrapper for a FB calls. Makes sure FB.init() was already executed, otherwise will wait until it is.
 *
 * @param callback
 */
function fbEnsureInit(callback) {
    if (typeof FB == 'undefined') {
        setTimeout(function () {
            fbEnsureInit(callback)
        }, 50);
    } else if (callback) {
        callback();
    }
}

function processFbButton() {
    var fbContainer = jQuery('.fb-like');

    if (jQuery(window).width() < tabletWidth) {
        if (fbContainer.attr('data-send')) {
            fbContainer.removeAttr('data-send');
            FB.XFBML.parse();
        }
    } else {
        if (typeof fbContainer == 'undefined' || typeof fbContainer.attr('data-send') == 'undefined') {
            fbContainer.attr('data-send', true);
            FB.XFBML.parse();
        }
    }
}
