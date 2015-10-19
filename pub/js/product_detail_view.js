require([
    'lib/domReady',
    'common',
    'recently_viewed_products',
    'lib/styleselect',
    'lib/zoom',
    'lib/swiping_container'
], function(domReady, common, recentlyViewedProducts, styleSelect, zoom, toggleSwipingArrows) {

    var tabletWidth = 768,
        siteFullWidth = 975;

    domReady(function() {
        styleSelect('select');

        handleRecentlyViewedProducts();

        adjustToPageWidth();
        window.addEventListener('resize', adjustToPageWidth);
        window.addEventListener('orientationchange', adjustToPageWidth);

        handleProductImages();
        initializeZoom();
        initializeTabs();
        fixIOS5Bug();

        require([
            '//connect.facebook.net/de_DE/all.js#xfbml=1&status=0',
            '//platform.twitter.com/widgets.js',
            '//apis.google.com/js/plusone.js'
        ]);
    });

    function handleRecentlyViewedProducts() {
        var recentlyViewedProductsListHtml = recentlyViewedProducts.getRecentlyViewedProductsHtml(product);

        if (recentlyViewedProductsListHtml.indexOf('</li>') !== -1) {
            var container = document.querySelector('#recently-viewed-products .swipe-container');
            container.innerHTML = recentlyViewedProductsListHtml;
            container.parentNode.style.display = 'block';
        }

        recentlyViewedProducts.addProductIntoLocalStorage(product);
    }

    function handleProductImages() {
        var mainImage = document.querySelector('.main-image-area img'),
            thumbnails = document.querySelectorAll('.more-views a');

        Array.prototype.map.call(thumbnails, function (thumbnail) {
            thumbnail.addEventListener('click', function(event) {
                event.preventDefault();
                mainImage.src = this.getAttribute('data-image');
                mainImage.parentNode.href = this.getAttribute('href');
                initializeZoom();
            }, true);
        });
    }

    function initializeZoom() {
        new zoom(document.querySelector('.main-image-area'));
    }

    function initializeTabs() {
        var activeTab,
            activeTabContent;

        Array.prototype.map.call(document.querySelectorAll('ul.tabs a'), function (tabLink, index) {
            if (0 === index) {
                activeTab = tabLink;
                activeTab.className = 'active';

                activeTabContent = document.getElementById(activeTab.hash.slice(1));
                activeTabContent.style.display = 'block';
            }

            tabLink.addEventListener('click', function (event) {
                event.preventDefault();

                activeTab.className = '';
                activeTabContent.style.display = 'none';

                activeTab = event.target;
                activeTab.className = 'active';

                activeTabContent = document.getElementById(activeTab.hash.slice(1));
                activeTabContent.style.display = 'block';
            }, true);
        });
    }

    function fixIOS5Bug() {
        if (navigator.userAgent.match(/\(iP[^)]+OS\s5/)) {
            var disabledOptions = document.querySelectorAll('#switcher-container option:disabled');
            Array.prototype.map.call(disabledOptions, function (option) {
                option.parentNode.removeChild(option);
            });
        }
    }

    function adjustToPageWidth() {
        var currentWidth = document.body.clientWidth,
        /* Maybe it makes sense to initialize variables on load only ? */
            productTitle = document.querySelector('.product-essential h1'),
            brandLogo = document.getElementById('brandLogo'),
            socialIcons = document.querySelector('.socialSharing'),
            productTopContainer = document.querySelector('.product-shop > .top'),
            productControls = document.querySelector('.product-controls'),
            price = document.querySelector('.price-information'),
            productMainInfo = document.querySelector('.product-main-info'),
            similarProductsLink = document.querySelector('.similarProducts'),
            articleInformation = document.querySelector('.articleInformations');

        /* Phone only */
        if (currentWidth < tabletWidth) {
            var phoneTitlePlaceholder = document.getElementById('phoneTitlePlaceholder');

            if (!isParent(phoneTitlePlaceholder, productTitle)) {
                phoneTitlePlaceholder.appendChild(productTitle);
            }

            if (!isParent(phoneTitlePlaceholder, brandLogo)) {
                phoneTitlePlaceholder.appendChild(brandLogo);
            }

            if (!isParent(productTopContainer, price)) {
                productTopContainer.appendChild(price);
            }

            if (!isParent(productControls, similarProductsLink)) {
                productControls.appendChild(similarProductsLink);
            }

            if (!isParent(productControls, articleInformation)) {
                productControls.appendChild(articleInformation);
            }

            if (!isParent(productControls, socialIcons)) {
                productControls.appendChild(socialIcons);
            }

            /* TODO: Implement image slider */

            /* Hide "send" part of FB buttons block if not yet hidden */
            fbEnsureInit(processFbButton);
        } else {
            var originalTitleContainer = document.querySelector('.product-title');

            if (!isParent(originalTitleContainer, brandLogo)) {
                originalTitleContainer.appendChild(brandLogo);
            }

            if (!isParent(originalTitleContainer, productTitle)) {
                originalTitleContainer.appendChild(productTitle);
            }

            if (!isParent(originalTitleContainer, similarProductsLink)) {
                originalTitleContainer.appendChild(similarProductsLink);
            }

            if (!isParent(productTopContainer, articleInformation)) {
                productTopContainer.appendChild(articleInformation);
            }

            if (!isParent(productTopContainer, socialIcons)) {
                productTopContainer.appendChild(socialIcons);
            }

            if (!isParent(productMainInfo, price)) {
                productMainInfo.appendChild(price);
            }

            /* Revert "send" part of FB buttons block if not yet recovered */
            fbEnsureInit(processFbButton);
        }

        /* Tablet only */
        if (currentWidth < siteFullWidth && currentWidth >= tabletWidth) {

            if (!isParent(productTopContainer, price)) {
                productTopContainer.appendChild(price);
            }

            if (!isParent(productTopContainer, articleInformation)) {
                productTopContainer.appendChild(articleInformation);
            }

        } else if (currentWidth >= siteFullWidth) {

            if (!isParent(productMainInfo, price)) {
                productMainInfo.appendChild(price);
            }

            if (!isParent(productTopContainer, articleInformation)) {
                productTopContainer.appendChild(articleInformation);
            }
        }

        toggleSwipingArrows('.swipe-container', 'ul');
    }

    function isParent(parent, child) {
        var node = child.parentNode;
        while (node != null) {
            if (node == parent) {
                return true;
            }
            node = node.parentNode;
        }
        return false;
    }

    /**
     * Wrapper for a FB calls. Makes sure FB.init() was already executed, otherwise will wait until it is.
     */
    function fbEnsureInit(callback) {
        if (typeof FB == 'undefined') {
            setTimeout(function () { fbEnsureInit(callback) }, 50);
        } else if (callback) {
            callback();
        }
    }

    function processFbButton() {
        var fbContainer = document.querySelector('.fb-like');

        if (document.body.clientWidth < tabletWidth) {
            if (fbContainer.getAttribute('data-send')) {
                fbContainer.removeAttribute('data-send');
                FB.XFBML.parse();
            }
        } else {
            if (typeof fbContainer == 'undefined' || typeof fbContainer.getAttribute('data-send') == 'undefined') {
                fbContainer.setAttribute('data-send', true);
                FB.XFBML.parse();
            }
        }
    }
});
