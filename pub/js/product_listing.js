require([
    'lib/domReady',
    'product_grid',
    'filter_navigation',
    'pagination',
    'lib/url',
    'lib/styleselect',
    'lib/overflow_scrolling',
    'common',
    'ekomi'
], function (domReady, productGrid, filterNavigation, pagination, url, styleSelect, productTitleScrolling) {

        var previousViewportWidth;

        domReady(function () {
            productGrid.renderGrid(productListingJson, '#products-grid-container');
            productTitleScrolling('.grid-cell-container h2');
            filterNavigation.renderLayeredNavigation(filterNavigationJson, '#filter-navigation');
            pagination.renderPagination(totalNumberOfResults, productsPerPage, '#pagination');
            setTotalNumberOfProductsInSelection(totalNumberOfResults, '.toolbar .amount');
            renderProductsPerPageLinks(productsPerPage, '.toolbar .limiter');
            renderSortingDropDown(sortOrderConfig, '.toolbar .sort-by');
            bindLayeredNavigationButtonsActions();

            adjustToPageWidth();
            window.addEventListener('resize', adjustToPageWidth);
            window.addEventListener('orientationchange', adjustToPageWidth);
        });

        function adjustToPageWidth() {
            if (!isViewportWidthChanged()) {
                return;
            }

            addClassToLastElementOfEachRow('last');
        }

        function isViewportWidthChanged() {
            if (document.body.clientWidth === previousViewportWidth) {
                return false;
            }

            previousViewportWidth = document.body.clientWidth;
            return true;
        }

        function addClassToLastElementOfEachRow(className) {
            var grid = document.querySelector('.products-grid'),
                cells = Array.prototype.slice.call(grid.querySelectorAll('li')),
                colsPerRow = Math.floor(grid.clientWidth / cells[0].clientWidth);

            cells.map(function (cell, index) {
                cell.className = cell.className.replace(/\blast\b/ig, '');
                if (!((index + 1) % colsPerRow)) {
                    cell.className += ' ' + className;
                }
            });
        }

        function setTotalNumberOfProductsInSelection(totalNumberOfResults, selector) {
            Array.prototype.map.call(document.querySelectorAll(selector), function (targetElement) {
                var textNode = document.createTextNode(totalNumberOfResults);
                targetElement.insertBefore(textNode, targetElement.firstChild);
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

            var link = document.createElement('A'),
                newUrl = url.updateQueryParameter('limit', numberOfProductsPerPage['number']);

            link.textContent = numberOfProductsPerPage['number'];
            link.href = url.removeQueryParameterFromUrl(newUrl, pagination.getPaginationQueryParameterName());

            return link;
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
            var sortingOption = document.createElement('OPTION'),
                newUrl = url.updateQueryParameters({ "order": config['code'], "dir": config['selectedDirection'] });

            sortingOption.textContent = getAttributeTranslation(config['code']);
            sortingOption.value = url.removeQueryParameterFromUrl(newUrl, pagination.getPaginationQueryParameterName());
            sortingOption.selected = config['selected'];

            return sortingOption;
        }

        function bindLayeredNavigationButtonsActions() {
            var filtersButton = document.getElementById('filters-button'),
                filters = document.getElementById('filter-navigation');

            filtersButton.addEventListener('click', function () {
                this.className = this.className.replace(/\bexpanded\b|\bcollapsed\b/ig, '');
                this.className += 'block' === filters.style.display ? ' collapsed' : ' expanded';
                filters.style.display = 'block' === filters.style.display ? 'none' : 'block';
            }, true);
        }

        function getAttributeTranslation(string) {
            if (typeof translations !== 'object' || !translations.hasOwnProperty(string)) {
                return string;
            }

            return translations[string];
        }
    }
);
