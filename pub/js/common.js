define(['lib/domReady', 'lib/cookie', 'search_autosuggestion'], function (domReady, cookie) {
    var tabletWidth = 768,
        siteFullWidth = 975,
        navItemsOriginalWidth = [];

    domReady(function () {
        window.addEventListener('resize', adjustToPageWidth);
        window.addEventListener('orientationchange', adjustToPageWidth);

        collectInitialNavigationTopItemsWidths();
        initializePhoneMenu();
        adjustToPageWidth();
        processLoginLogoutMetaLinks();
        processCartMetaInfo();
    });

    function collectInitialNavigationTopItemsWidths() {
        Array.prototype.map.call(document.querySelectorAll('.nav > li'), function (menuItem) {
            navItemsOriginalWidth.push(menuItem.offsetWidth);
        });
    }

    function initializePhoneMenu() {
        var allPhoneMetaMenuItems = document.querySelectorAll('#phone-meta-menu li'),
            phoneMetaMenuContent = document.getElementById('phone-meta-menu-content');

        Array.prototype.map.call(document.querySelectorAll('#phone-meta-menu li a'), function (menuItem) {
            if (menuItem.getAttribute('data-block') === null) {
                return;
            }

            menuItem.addEventListener('click', function (event) {
                event.preventDefault();

                phoneMetaMenuContent.className = this.getAttribute('data-block');

                removeClassFromAllNodeListElements(allPhoneMetaMenuItems, 'active');
                this.parentNode.className += ' active';
            }, true);
        });
    }

    function processLoginLogoutMetaLinks() {
        var elementIdToHide = 'meta-menu-logout-link';

        if (cookie.getJsonValue('lizardsAndPumpkinsTransport', 'isCustomerLoggedIn')) {
            elementIdToHide = 'meta-menu-login-link';
        }

        var elementToHide = document.getElementById(elementIdToHide);
        elementToHide.style.display = 'none';
    }

    function processCartMetaInfo() {
        var cartNumItems = cookie.getJsonValue('lizardsAndPumpkinsTransport', 'cartNumItems');

        if (cartNumItems) {
            var cartNumItemsElement = document.getElementById('meta-menu-cart-num-items');
            cartNumItemsElement.innerHTML = cartNumItems;
        }

        var cartTotal = cookie.getJsonValue('lizardsAndPumpkinsTransport', 'cartTotal');

        if (cartTotal) {
            var cartTotalElement = document.getElementById('meta-menu-cart-total');
            cartTotalElement.innerHTML = cartTotal;
        }
    }

    function toggleMainNavigationSubMenu(event) {
        event.preventDefault();

        var elementWasAlreadySelected = this.parentNode.className.match(/\bhover\b/ig);

        removeClassFromAllNodeListElements(document.querySelectorAll('.nav > li'), 'hover');

        if (null === elementWasAlreadySelected) {
            this.parentNode.className += ' hover';
        }
    }

    function adjustToPageWidth() {
        if (document.body.clientWidth < siteFullWidth) {
            Array.prototype.map.call(document.querySelectorAll('.nav > li.parent > a'), function (menuItem) {
                menuItem.addEventListener('click', toggleMainNavigationSubMenu);
            });
        }

        if (document.body.clientWidth < tabletWidth) {
            var footerBlockTitles = document.querySelectorAll('.footer-block .footer-block-title');
            Array.prototype.map.call(footerBlockTitles, function (footerBlockTitle) {
                footerBlockTitle.addEventListener('click', toggleFooterBlockPhone, true);
            });
        }

        recalculateMainMenu();
        handleMainNavigationSoftWrapping();
    }

    function toggleFooterBlockPhone() {
        if (this.parentNode.className.match(/\bexpanded\b/ig)) {
            this.parentNode.className = this.parentNode.className.replace(/\bexpanded\b/ig, '');
        } else {
            this.parentNode.className += ' expanded';
        }
    }

    function handleMainNavigationSoftWrapping() {
        var currentWidth = document.body.clientWidth,
            nav = Array.prototype.slice.call(document.querySelectorAll('.nav'));

        if (0 === nav.length) {
            return;
        }

        if (Math.ceil(nav[0].offsetWidth / currentWidth) === nav.length && nav[0].offsetWidth <= currentWidth) {
            return;
        }

        var widthSoFar = 0,
            newNav;

        Array.prototype.map.call(document.querySelectorAll('.nav > li'), function (item, index) {
            item.className = item.className.replace(/\bfirst\b|\blast\b/ig, '');
            if (0 === widthSoFar) {
                newNav = document.createElement('UL');
                newNav.className = 'nav';
                item.className += ' first';
            }
            if (0 === index) {
                newNav.className += ' first';
            }
            if (navItemsOriginalWidth.length - 1 === index) {
                newNav.className += ' last';
            }

            widthSoFar += navItemsOriginalWidth[index];

            var noMoreItemsWillFitIntoThisLine = widthSoFar + navItemsOriginalWidth[index + 1] > currentWidth,
                noMoreItemsLeft = typeof navItemsOriginalWidth[index + 1] == 'undefined';

            if (noMoreItemsWillFitIntoThisLine || noMoreItemsLeft) {
                item.className += ' last';
            }

            newNav.appendChild(item);

            if (noMoreItemsWillFitIntoThisLine || noMoreItemsLeft) {
                widthSoFar = 0;
                nav[0].parentNode.appendChild(newNav);
            }
        });

        nav.map(function(menu) {
            menu.parentNode.removeChild(menu);
        });
    }

    function recalculateMainMenu() {
        var navWidth = Math.min(document.body.clientWidth, siteFullWidth),
            numColumns = Math.floor(navWidth / 240),
            itemWidth = Math.floor(navWidth / numColumns);

        Array.prototype.map.call(document.querySelectorAll('.nav li.level0 ul'), function (subMenu) {
            var items = Array.prototype.slice.call(subMenu.querySelectorAll('li')),
                numMenuItems = items.length,
                additionalHeight = -40;

            if (subMenu.className.split(' ').indexOf('brands') > -1) {
                items = items.slice(3, -1);
                additionalHeight = 70;
                numMenuItems -= 2;
            }

            var itemsPerColumn = Math.ceil(numMenuItems / numColumns),
                linkHeight = items[0].offsetHeight,
                wrappedItemsHeight = linkHeight * (numMenuItems - itemsPerColumn);

            subMenu.style.height = (subMenu.offsetHeight - wrappedItemsHeight + additionalHeight) + 'px';

            items.map(function (item, iterator) {
                item.style.width = itemWidth + 'px';

                var columnIndex = Math.floor(iterator / itemsPerColumn);
                if (columnIndex > 0) {
                    item.style.position = 'relative';
                    item.style.top = '-' + (itemsPerColumn * linkHeight * columnIndex) + 'px';
                    item.style.left = columnIndex * itemWidth + 'px';
                }
            })
        });
    }

    function removeClassFromAllNodeListElements(nodeList, className) {
        Array.prototype.map.call(nodeList, function (nodeListItem) {
            var regExp = new RegExp('\\b' + className + '\\b', 'ig');
            nodeListItem.className = nodeListItem.className.replace(regExp, '');
        });
    }
});
