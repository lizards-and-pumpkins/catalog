require([
    'lib/domReady',
    'common',
    'recently_viewed_products',
    'related_models',
    'lib/styleselect',
    'lib/zoom',
    'lib/swiping_container'
], function(domReady, common, recentlyViewedProducts, loadRelatedModels, styleSelect, zoom, toggleSwipingArrows) {

    var tabletWidth = 768,
        selectBoxIdPrefix = 'variation_',
        addToCartButton;

    domReady(function() {
        renderPrices();
        handleRecentlyViewedProducts();
        initializeAddToCartButton();
        showNextSelectBox();

        adjustToPageWidth();
        window.addEventListener('resize', adjustToPageWidth);
        window.addEventListener('orientationchange', adjustToPageWidth);

        handleProductImages();
        initializeZoom();
        initializeTabs();
        showAvailabilityStatus();
        loadRelatedModels(document.querySelector('meta[itemprop="sku"]').content);
    });

    function renderPrices() {
        var regularPrice = document.getElementById('regular-price'),
            oldPrice = document.getElementById('old-price');

        if (null === regularPrice || typeof window.regularPrice === 'undefined') {
            return;
        }

        if ('' === window.specialPrice) {
            regularPrice.textContent = window.regularPrice;
            return;
        }

        oldPrice.textContent = window.regularPrice;
        regularPrice.textContent = window.specialPrice;
    }

    function initializeAddToCartButton() {
        addToCartButton = document.querySelector('.product-controls button');
        addToCartButton.addEventListener('click', function() {
            var productId = document.querySelector('input[name="product"]').value,
                qty = document.getElementById(selectBoxIdPrefix + 'qty').value;

            document.location.href = baseUrl + 'cart/cart/add/sku/' + productId + '/qty/' + qty + '/';
        }, true);
    }

    function deleteAllSelectBoxesAfter(previousBoxAttribute) {
        var attributeCodes = variation_attributes.slice(variation_attributes.indexOf(previousBoxAttribute) + 1);
        attributeCodes.push('qty');

        attributeCodes.map(function (code) {
            var selectBoxToDelete = document.getElementById(selectBoxIdPrefix + code);
            if (null !== selectBoxToDelete) {
                var styledSelectUuid = selectBoxToDelete.getAttribute('data-ss-uuid'),
                    styledSelect = document.querySelector('div[data-ss-uuid="' + styledSelectUuid + '"]');
                selectBoxToDelete.parentNode.removeChild(selectBoxToDelete);
                styledSelect.parentNode.removeChild(styledSelect);
            }
        });
    }

    function getSelectedVariationValues() {
        return variation_attributes.reduce(function(carry, attributeCode) {
            var selectBox = document.getElementById(selectBoxIdPrefix + attributeCode);
            if (null !== selectBox) {
                carry[attributeCode] =  selectBox.value;
            }
            return carry;
        }, {});
    }

    function getAssociatedProductsMatchingSelection() {
        var selectedAttributes = getSelectedVariationValues();

        return associated_products.filter(function (product) {
            return Object.keys(product['attributes']).reduce(function (carry, attributeCode) {
                if (false === carry) {
                    return carry;
                }
                return Object.keys(selectedAttributes).reduce(function (carry, selectedAttributeCode) {
                    if (false === carry || selectedAttributeCode !== attributeCode) {
                        return carry;
                    }
                    return selectedAttributes[selectedAttributeCode] === product['attributes'][attributeCode];
                }, carry);
            }, true);
        });
    }

    function isConfigurableProduct() {
        return (typeof variation_attributes === 'object') &&
            (typeof associated_products === 'object') &&
            (variation_attributes.length > 0);
    }

    function showNextSelectBox(previousBoxAttribute) {
        var selectContainer = document.querySelector('.selects'),
            productIdField = document.querySelector('input[name="product"]');

        if (!isConfigurableProduct()) {
            var stockQuantity = parseInt(stockQty, 10);
            if (stockQuantity > 0) {
                showQtyBoxAndReleaseAddToCartButton(selectContainer, stockQuantity);
            }
            return;
        }

        productIdField.value = '';
        addToCartButton.disabled = 'disabled';

        if (previousBoxAttribute) {
            deleteAllSelectBoxesAfter(previousBoxAttribute);
            if ('' === document.getElementById(selectBoxIdPrefix + previousBoxAttribute).value) {
                return;
            }
        }

        var matchingProducts = getAssociatedProductsMatchingSelection(),
            variationAttributeCode = variation_attributes[variation_attributes.indexOf(previousBoxAttribute) + 1];

        if (typeof variationAttributeCode === 'undefined') {
            var selectedProductStock = matchingProducts[0]['attributes']['stock_qty'];
            productIdField.value = matchingProducts[0]['product_id'];
            showQtyBoxAndReleaseAddToCartButton(selectContainer, selectedProductStock);
            return;
        }

        addVariationSelectBox(matchingProducts, variationAttributeCode, selectContainer);
    }

    function showQtyBoxAndReleaseAddToCartButton(parentContainer, maxQty) {
        parentContainer.appendChild(createQtySelectBox(maxQty));
        styleSelect('#' + selectBoxIdPrefix + 'qty');
        addToCartButton.disabled = '';
    }

    function addVariationSelectBox(matchingProducts, variationAttributeCode, selectContainer) {
        var options = getVariationAttributeOptionValuesArray(matchingProducts, variationAttributeCode);
        selectContainer.appendChild(createSelect(variationAttributeCode, options));
        styleSelect('#' + selectBoxIdPrefix + variationAttributeCode);
    }

    function createQtySelectBox(maxQty) {
        var select = document.createElement('SELECT');

        select.id = selectBoxIdPrefix + 'qty';

        for (var i = 1; i <= maxQty; i++) {
            var option = document.createElement('OPTION');
            option.textContent = i;
            option.value = i;
            select.appendChild(option);
        }

        return select;
    }

    function getVariationAttributeOptionValuesArray(products, attributeCode) {
        return products.reduce(function (carry, associatedProduct) {
            var optionIsAlreadyPresent = false;

            for (var i=0; i<carry.length; i++) {
                if (carry[i]['value'] === associatedProduct['attributes'][attributeCode]) {
                    optionIsAlreadyPresent = true;

                    if (true === carry[i]['disabled'] && associatedProduct['attributes']['stock_qty'] > 0) {
                        carry[i]['disabled'] = false;
                    }
                }
            }

            if (false === optionIsAlreadyPresent) {
                carry.push({
                    'value': associatedProduct['attributes'][attributeCode],
                    'disabled': 0 == associatedProduct['attributes']['stock_qty']
                });
            }

            return carry;
        }, []);
    }

    function createSelect(name, options) {
        var variationSelect = document.createElement('SELECT');
        variationSelect.id = selectBoxIdPrefix + name;
        variationSelect.addEventListener('change', function () { showNextSelectBox(name); }, true);

        var defaultOption = document.createElement('OPTION');
        defaultOption.textContent = 'Select ' + name;
        variationSelect.appendChild(defaultOption);

        options.map(function (option) {
            variationSelect.appendChild(createSelectOption(option));
        });

        return variationSelect;
    }

    function createSelectOption(option) {
        var variationOption = document.createElement('OPTION');
        variationOption.textContent = option['value'];
        variationOption.value = option['value'];

        if (option['disabled']) {
            variationOption.disabled = 'disabled';
        }

        return variationOption;
    }

    function handleRecentlyViewedProducts() {
        recentlyViewedProducts.addProductIntoLocalStorage(product);
        var recentlyViewedProductsListHtml = recentlyViewedProducts.getRecentlyViewedProductsHtml(product);

        if (recentlyViewedProductsListHtml.indexOf('</li>') !== -1) {
            var container = document.querySelector('#recently-viewed-products .swipe-container');
            container.innerHTML = recentlyViewedProductsListHtml;
            container.parentNode.style.display = 'block';
        }
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

    function adjustToPageWidth() {
        var currentWidth = document.body.clientWidth,
        /* Maybe it makes sense to initialize variables on load only ? */
            productTitle = document.querySelector('.product-essential h1'),
            brandLogo = document.getElementById('brandLogo'),
            productTopContainer = document.querySelector('.product-shop > .top'),
            productControls = document.querySelector('.product-controls'),
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

            if (!isParent(productControls, similarProductsLink)) {
                productControls.appendChild(similarProductsLink);
            }

            if (!isParent(productControls, articleInformation)) {
                productControls.appendChild(articleInformation);
            }

            /* TODO: Implement image slider */
            /* TODO: From 767px to 366px an "original" image could be used and below 366px a "large" one. */
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
        }

        toggleSwipingArrows('.swipe-container', 'ul');
    }

    function showAvailabilityStatus() {
        if (!isInStock()) {
            var inStockLabel = document.querySelector('.in-stock'),
                outOfStockLabel = document.querySelector('.out-of-stock');

            inStockLabel.style.display = 'none';
            outOfStockLabel.style.display = 'inline';
        }
    }

    function isInStock() {
        if (isConfigurableProduct() && atLeastOneAssociatedProductIsInStock()) {
            return true;
        }

        return !isConfigurableProduct() && parseInt(stockQty) > 0;
    }

    function atLeastOneAssociatedProductIsInStock() {
        var numberOfAssociatedProducts = associated_products.length;

        for (var i = 0; i < numberOfAssociatedProducts; i++) {
            if (parseInt(associated_products[i]['attributes']['stock_qty'], 10) > 0) {
                return true;
            }
        }

        return false;
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
});
