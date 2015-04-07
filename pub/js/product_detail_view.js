require([
    'common',
    'recently_viewed_products',
    'lib/jquery.jqzoom.min',
    'lib/jquery.uniform.min'
], function(common, recentlyViewedProducts) {

    jQuery(document).ready(function() {
        require([
            '//connect.facebook.net/de_DE/all.js#xfbml=1&status=0',
            '//platform.twitter.com/widgets.js',
            '//apis.google.com/js/plusone.js'
        ]);

        adjustDetailsToWidth();
        jQuery(window).bind('resize orientationchange', adjustDetailsToWidth);

        handleRecentlyViewedProducts();
        initializeZoom();
        initializeTabs();
        observeSizeDropDown();
        //initiateOverlays();
        initiateFullScreenImageViewer();
        initiateYouTubePlayer();
        initiate360View();
        fixIOS5Bug();
    });

    function handleRecentlyViewedProducts() {
        var recentlyViewedProductsListHtml = recentlyViewedProducts.getRecentlyViewedProductsHtml(product);

        if (-1 != recentlyViewedProductsListHtml.indexOf('</li>')) {
            jQuery('#recently-viewed-products').show()
                .find('.swipe-container').eq(0).html(recentlyViewedProductsListHtml);
        }

        recentlyViewedProducts.addProductIntoLocalStorage(product);
    }

    function initializeZoom() {
        jQuery('.main-image-area').jqzoom({
            'zoomWidth': 595,
            'zoomHeight': 389,
            'xOffset': 5,
            'title': false,
            'preloadText': ''
        });
    }

    function initializeTabs() {
        jQuery('ul.tabs').each(function() {
            var links = jQuery(this).find('a'),
                active = jQuery(links.filter('[href="' + location.hash + '"]')[0] || links[0]).addClass('active'),
                content = jQuery(active[0].hash);

            links.not(active).each(function () {
                jQuery(this.hash).hide();
            });

            jQuery(this).on('click', 'a', function(e){
                active.removeClass('active');
                content.hide();

                active = jQuery(this);
                content = jQuery(this.hash);

                active.addClass('active');
                content.show();

                e.preventDefault();
            });
        });
    }

    function observeSizeDropDown() {
        jQuery('div#productView select').not('#qty').eq(0).bind('change', function (event) {
            var selected_item = jQuery(event.target).find(':selected'),
                selected_item_sold_out = !jQuery(event.target).length ||
                    typeof selected_item.attr('disabled') != 'undefined' ||
                    jQuery(event.target).prop('selectedIndex') == 0,
                qty_box = jQuery('.qty-box'),
                buttonInstance = jQuery('#productView').find('button[type=submit]'),
                selectedItemStockAvailable = selected_item.attr('stock');

            jQuery('.product-shop span.availability').hide();
            if (selectedItemStockAvailable && selectedItemStockAvailable.match(/[0-9]+/)) {
                jQuery('.product-shop span.selectedSize').html(selected_item.text());
                jQuery('.product-shop span.availability' + (selected_item_sold_out ? '.false' : '.true')).show();
                var qty = qty_box.find('select');
                qty.find('option').remove();
                for (var i = 0; i < selectedItemStockAvailable; i++) {
                    qty.append(jQuery('<option>' + (i + 1) + '</option>'));
                }
                jQuery.uniform.update('.qty-box select');
            } else {
                jQuery('.product-shop span.availability.choose').show();
                jQuery('.product-shop .selectedSize').html('');
            }
            selected_item_sold_out ? qty_box.hide() : qty_box.show();
            selected_item_sold_out ? buttonInstance.attr('disabled', 'disabled') : buttonInstance.removeAttr('disabled');
        }).trigger('change');
    }

    function initiateOverlays() {
        jQuery('.product-shop .productAlert').bind('click', function () {
            /* Show popup */
            new ModalBox().show(jQuery('#alertLayer').html());

            /* Apply uniform for drop-down */
            jQuery('.modal-popup .alertPopUp select').uniform({selectAutoWidth: false});

            /* Bind action to button */
            jQuery('.alertPopUp button').on('click', function () {
                location.href = jQuery(this).parents('.alertPopUp').find('select').val();
            });
        });
        jQuery('.availability .info').bind('click', function () {
            new ModalBox().show(Mage.baseUrl + 'common/index/index/?identifier=versandinfo');
        });
    }

    function initiateFullScreenImageViewer() {
        if (typeof(slideshowSwf) == 'undefined') return;
        jQuery('div#productView div.slideshow').flash({
            swf: slideshowSwf,
            allowfullscreen: true,
            flashvars: {
                data: JSON.stringify(slideshowData)
            }
        });
    }

    function initiateYouTubePlayer() {
        var holder = jQuery('div#productView div.youTubeHolder');
        jQuery('div#productView div.product-essential div.product-img-box div.more-views a.youtube').click(function () {
            hideSwfObjects();
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
    }

    function initiate360View() {
        jQuery('div#productView div.product-essential div.product-img-box div.more-views a.panorama').click(function () {
            hideSwfObjects();
            var panorama = jQuery('div#productView div.panorama');
            panorama.flash({
                swf: panorama_swf,
                width: mediaWidth,
                height: mediaHeight
            });
            panorama.show();
        });
    }

    function hideSwfObjects() {
        jQuery('div#productView div.panorama, div#productView div.youTubeHolder, div#productView div.slideshow, div#productView div.main-image-area-wrapper div#wrap div.mousetrap').hide();
        jQuery('div#productView div.panorama, div#productView div.youTubeHolder').find('object, embed').detach();
    }

    function fixIOS5Bug() {
        /**
         * iOS5 has a problem disabling <select/> elements. Hence such elements are removed if such device is detected.
         */
        if (navigator.userAgent.match(/\(iP[^)]+OS\s5/)) {
            jQuery('#switcher-container').find('option:disabled').remove();
        }
    }

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
});
