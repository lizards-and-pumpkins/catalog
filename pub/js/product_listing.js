require(
    ['lib/domReady', 'product_grid', 'filter_navigation', 'pagination', 'lib/url', 'lib/styleselect', 'common'],
    function (domReady, productGrid, filterNavigation, pagination, url, styleSelect) {
        domReady(function () {
            productGrid.renderGrid(productListingJson, productPrices, '#products-grid-container');
            filterNavigation.renderLayeredNavigation(filterNavigationJson, '#filter-navigation');
            pagination.renderPagination(totalNumberOfResults, productsPerPage, '#pagination');
            setTotalNumberOfProductsInSelection(totalNumberOfResults, '.toolbar .amount');
            renderProductsPerPageLinks(productsPerPage, '.toolbar .limiter');
            renderSortingDropDown(sortOrderConfig, '.toolbar .sort-by');
        });

        function setTotalNumberOfProductsInSelection(totalNumberOfResults, selector) {
            Array.prototype.map.call(document.querySelectorAll(selector), function (targetElement) {
                var textNode = document.createTextNode(totalNumberOfResults + ' Items');
                targetElement.appendChild(textNode);
            });
        }

        function renderProductsPerPageLinks(productsPerPage, selector) {
            var productsPerPageLinksPlaceholder = document.querySelector(selector);

            if (null === productsPerPageLinksPlaceholder) {
                return;
            }

            productsPerPage.map(function (numberOfProductsPerPage, index) {
                productsPerPageLinksPlaceholder.appendChild(createProductsPerPageElement(numberOfProductsPerPage));

                if (index < productsPerPage.length - 1) {
                    var separator = document.createTextNode(' | ');
                    productsPerPageLinksPlaceholder.appendChild(separator);
                }
            });
        }

        function createProductsPerPageElement(numberOfProductsPerPage) {
            if (true === numberOfProductsPerPage['selected']) {
                return document.createTextNode(numberOfProductsPerPage['number']);
            }

            var productsPerPageElement = document.createElement('A');
            productsPerPageElement.textContent = numberOfProductsPerPage['number'];
            productsPerPageElement.href = url.updateQueryParameter('limit', numberOfProductsPerPage['number']);

            return productsPerPageElement;
        }

        function renderSortingDropDown(sortOrderConfig, selector) {
            if (typeof sortOrderConfig !== 'object' || 0 === sortOrderConfig.length) {
                return;
            }

            var sortingPlaceholder = document.querySelector(selector);

            if (null === sortingPlaceholder) {
                return;
            }

            sortingPlaceholder.appendChild(createSortingSelect(sortOrderConfig));
            styleSelect(selector + ' select');
        }

        function createSortingSelect(sortOrderConfig) {
            var sortingSelect = document.createElement('SELECT');

            sortingSelect.addEventListener('change', function () {
                document.location.href = this.value
            }, true);

            sortOrderConfig.map(function (config) {
                sortingSelect.appendChild(createSortingSelectOption(config));
            });

            return sortingSelect;
        }

        function createSortingSelectOption(config) {
            var sortingOption = document.createElement('OPTION');
            sortingOption.textContent = config['code'];
            sortingOption.value = url.updateQueryParameters({
                "order": config['code'],
                "dir": config['selectedDirection']
            });
            sortingOption.selected = config['selected'];

            return sortingOption;
        }
    }
);
