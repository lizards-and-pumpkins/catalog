define(
    ['lib/domReady', 'jquery', 'lib/cookie', 'search_autosuggestion'],
    function (domReady, jQuery, cookie) {
        var nav,
            tabletWidth = 768,
            siteFullWidth = 975,
            currentPhoneMenuItem,
            store,
            lang,
            menuItemPosition = {search: 2, lang: 3, store: 4},
            navItemsOriginalWidth = [],
            phoneMenuSize;

        domReady(function () {
            nav = jQuery('.nav');
            nav.children().each(function (index) {
                navItemsOriginalWidth[index] = jQuery(this).width();
            });
            nav.find('li a').each(function () {
                if (jQuery(this).next().length > 0) {
                    jQuery(this).addClass('parent');
                }
            });
            var phoneMenu = jQuery('.phone-bar');
            phoneMenuSize = phoneMenu.find('li').length;

            /* Set phone menu item width */
            var cellWidth = Math.round(100 / phoneMenuSize);
            jQuery('#phoneMenuPlaceholder').find('li').width(cellWidth + '%');
            phoneMenu.find('li').width(cellWidth + '%').last().addClass('last').width((cellWidth - 1) + '%');

            adjustToPageWidth();
            jQuery(window).bind('resize orientationchange', adjustToPageWidth);

            processLoginLogoutMetaLinks();
            processCartMetaInfo();
        });

        function processLoginLogoutMetaLinks() {

            var selectorToHide = '#meta-menu-logout-link';

            if (cookie.getJsonValue('breraTransport', 'isCustomerLoggedIn')) {
                selectorToHide = '#meta-menu-login-link';
            }

            var elementToHide = document.querySelector(selectorToHide);
            elementToHide.style.display = 'none';
        }

        function processCartMetaInfo() {

            var cartNumItems = cookie.getJsonValue('breraTransport', 'cartNumItems');

            if (cartNumItems) {
                var cartNumItemsElement = document.querySelector('#meta-menu-cart-num-items');
                cartNumItemsElement.innerHTML = cartNumItems;
            }

            var cartTotal = cookie.getJsonValue('breraTransport', 'cartTotal');

            if (cartTotal) {
                var cartTotalElement = document.querySelector('#meta-menu-cart-total');
                cartTotalElement.innerHTML = cartTotal;
            }
        }

        function adjustToPageWidth() {
            var currentWidth = jQuery(window).width(),
                placeholder = jQuery('#navPlaceholder'),
                bannerBlockLeft = jQuery('.banner-wrap');

            if (currentWidth < siteFullWidth) {
                /* Create sub-menu placeholder if not exists */
                if (!placeholder.length) {
                    placeholder = jQuery('<div id="navPlaceholder"></div>').insertAfter(nav);
                }

                nav.find('li').unbind('mouseenter mouseleave');
                nav.find('li a.parent').unbind('click').bind('click', function (e) {
                    // must be attached to anchor element to prevent bubbling
                    e.preventDefault();
                    var parentLi = jQuery(this).parent('li');
                    parentLi.toggleClass('hover');

                    /* Loop through sub-menus and open/close appropriate */
                    toggleMobileNav(parentLi, placeholder);
                });

                /* Initialize stores and languages */
                if (!store && phoneMenuSize > 3) {
                    store = initializeListObject('store');
                }

                if (!lang && phoneMenuSize > 4) {
                    lang = initializeListObject('lang');
                }
            } else {
                nav.find('li')
                    .removeClass('hover')
                    .unbind('mouseenter mouseleave')
                    .bind('mouseenter', function () {
                        jQuery(this).addClass('hover');
                    })
                    .bind('mouseleave', function () {
                        jQuery(this).removeClass('hover');
                    })
                    .find('a')
                    .unbind('click');
                placeholder.remove();
            }

            if (currentWidth < tabletWidth) {

                /* By default set selected meta-menu item to search */
                if (!currentPhoneMenuItem || currentPhoneMenuItem == 'search') {
                    currentPhoneMenuItem = null;
                    togglePhoneBlock('search');
                }

                /* Observe click on footer menu headings. Unbind first to avoid multiple bindings. */
                jQuery('.footer-block .footer-block-title').unbind('click', toggleFooterBlockPhone)
                    .bind('click', toggleFooterBlockPhone)
                    .next().hide();

                /* Move left banner block under top-sellers (only for home) */
                if (!bannerBlockLeft.hasClass('downtown')) {
                    bannerBlockLeft.insertAfter(jQuery('#topsellerTabs')).addClass('downtown');
                }
            } else {
                /* Remove observer from footer menu headings */
                jQuery('.footer-block .footer-block-title').unbind('click', toggleFooterBlockPhone)
                    .next().show();

                /* Append the search back if needed */
                var searchPanel = jQuery('#searchPanel');
                if (!searchPanel.find('#search_mini_form').length) {
                    searchPanel.append(jQuery('#search_mini_form'));
                }

                /* Move left banner block back */
                if (bannerBlockLeft.hasClass('downtown')) {
                    bannerBlockLeft.insertAfter(jQuery('.slider-wrap')).removeClass('downtown');
                }
            }

            recalculateMainMenu();

            /* Handle main navigation soft wrapping. This has to be done after placeholder is created */
            if (Math.ceil(nav.eq(0).width() / currentWidth) != nav.length || nav.eq(0).width() > currentWidth) {

                /* Move all menu items to a temporary storage */
                var temp_storage = jQuery('<ul class="temp-storage"></ul>');
                nav.children().each(function () {
                    temp_storage.append(jQuery(this));
                });
                temp_storage.insertAfter(nav);

                /* Kill all menus */
                nav.remove();

                /* Create new menu(s) */
                var widthSoFar = 0,
                    newNav,
                    last;
                temp_storage.children().each(function (index) {
                    jQuery(this).removeClass('first last');
                    if (!widthSoFar) {
                        newNav = jQuery('<ul class="nav"></ul>');
                        jQuery(this).addClass('first');
                    }
                    if (index == 0) {
                        newNav.addClass('first');
                    }
                    if (index == navItemsOriginalWidth.length - 1) {
                        newNav.addClass('last');
                    }
                    widthSoFar += navItemsOriginalWidth[index];
                    /* We are checking the width with the next element because if current element wider then the current
                     * view-port it will result in infinite loop */
                    last = widthSoFar + navItemsOriginalWidth[index + 1] > currentWidth || typeof navItemsOriginalWidth[index + 1] == 'undefined';
                    if (last) {
                        jQuery(this).addClass('last');
                    }
                    newNav.append(jQuery(this));
                    if (last) {
                        widthSoFar = 0;
                        newNav.insertBefore(temp_storage);
                    }
                });

                /* Kill temporary storage */
                jQuery('.temp-storage').remove();

                /* Initiate new menu(s) var */
                nav = jQuery('.nav');
            }
        }

        /**
         * Toggles footer blocks in phone mode.
         *
         * (Put as a separate function to avoid unbinding of other possible handlers that element may have.)
         */
        function toggleFooterBlockPhone() {
            jQuery(this).next().toggle();
        }

        function toggleMobileNav(parentLi, placeholder) {
            /* Clear placeholder */
            placeholder.html('');

            /* Loop through sub-menus and open/close appropriate */
            nav.children('li').each(function () {
                if (jQuery(this).attr('class') == parentLi.attr('class') && parentLi.hasClass('hover')) {
                    placeholder.append(jQuery(this).children('ul').eq(0).clone());
                } else {
                    jQuery(this).removeClass('hover');
                }
            });
        }

        function togglePhoneBlock(blockType) {
            if (currentPhoneMenuItem != blockType) {
                var phoneMenuPlaceholder = jQuery('#phoneMenuPlaceholder');
                var menuContent = phoneMenuPlaceholder.find('.content');

                if (blockType == 'search') {
                    /* Move search panel into placeholder */
                    if (!menuContent.find('#search_mini_form').length) {
                        menuContent.html('').append(jQuery('#search_mini_form'));
                    }
                } else {
                    /* Return the search panel back so it is not lost */
                    if (menuContent.find('#search_mini_form').length) {
                        jQuery('#searchPanel').append(jQuery('#search_mini_form'));
                    }
                    menuContent.html(window[blockType]);
                }

                phoneMenuPlaceholder.find('li').removeClass('active').eq(menuItemPosition[blockType]).addClass('active');
                jQuery('.phone-bar').find('li').removeClass('active').eq(menuItemPosition[blockType]).addClass('active');

                currentPhoneMenuItem = blockType;
            }
        }

        /**
         * Extract list of languages or stores from page markup
         *
         * @param type
         * @returns String
         */
        function initializeListObject(type) {
            var ids = {lang: 'language-select', store: 'part'};
            var container = jQuery('#' + ids[type]);

            var active = jQuery.trim(container.find('.drop').text());
            var menuImage = jQuery('.phone-bar img').eq(menuItemPosition[type]);
            var newSrc = active.toLowerCase().replace(/\./g, '-');
            var regexp = new RegExp('ç', 'g');
            newSrc = newSrc.replace(regexp, 'c');
            newSrc = menuImage.attr('src').replace(/icon-phone-[^.]+\.png/i, 'icon-phone-' + type + '-' + newSrc + '.png');
            menuImage.attr('src', newSrc);

            return container.find('.drop-list').html();
        }

        function recalculateMainMenu() {
            var navWidth = jQuery(window).width() < siteFullWidth ? jQuery(window).width() : siteFullWidth,
                numColumns = Math.floor(navWidth / 240),
                itemWidth = Math.floor(navWidth / numColumns),
                navPlaceholder = jQuery('#navPlaceholder'),
                navigation = navPlaceholder.length ? navPlaceholder : nav;

            navigation.find('ul').width(navWidth);
            navigation.find('li li').width(itemWidth + 'px');

            jQuery('li.level0 ul').each(function () {
                var items = jQuery(this).find('li'),
                    numMenuItems = items.length,
                    additionalHeight = 0;

                if (jQuery(this).hasClass('brands')) {
                    items = items.slice(3, -1);
                    additionalHeight = 70;
                    numMenuItems -= 2;
                }

                var itemsPerColumn = Math.ceil(numMenuItems / numColumns),
                    itemHeight = jQuery(items[0]).find('a').outerHeight(true);

                jQuery(this).height(jQuery(this).height() - itemHeight * (numMenuItems - itemsPerColumn) + additionalHeight);

                items.each(function (iterator) {
                    var columnIndex = Math.floor(iterator / itemsPerColumn);

                    jQuery(this).removeClass('first');

                    if (columnIndex > 0) {
                        jQuery(this).css('position', 'relative')
                            .css('top', '-' + (itemsPerColumn * itemHeight * columnIndex) + 'px')
                            .css('left', columnIndex * itemWidth + 'px');
                    }
                })
            });
        }
    }
);
