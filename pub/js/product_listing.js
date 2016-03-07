require(
    [
    'lib/domReady',
    'product_grid',
    'filter_navigation',
    'pagination',
    'lib/url',
    'lib/styleselect',
    'lib/overflow_scrolling',
    'lib/translate',
    'magento_data',
    'common',
    'ekomi'
],function (
    domReady,
    productGrid,
    filterNavigation,
    pagination,
    url,
    styleSelect,
    productTitleScrolling,
    translate,
    magentoData
) {

        var previousViewportWidth;

        domReady(function () {
            renderContent();

            filterNavigation.renderLayeredNavigation(filterNavigationJson, '#filter-navigation');
            bindLayeredNavigationButtonsActions();
            processGoogleTagManager();

            adjustToPageWidth();
            window.addEventListener('resize', adjustToPageWidth);
            window.addEventListener('orientationchange', adjustToPageWidth);
        });

        function renderContent() {
            var content = document.querySelector('.col-main');

            if (typeof totalNumberOfResults === 'undefined' || 0 === totalNumberOfResults) {
                content.appendChild(createEmptyListingBlock());
                return;
            }

            content.appendChild(createToolbar());
            productGrid.renderGrid(productListingJson, '.col-main');
            content.appendChild(pagination.renderPagination(totalNumberOfResults, productsPerPage));

            styleSelect('.sort-by select');
            productTitleScrolling('.grid-cell-container h2');
        }

        function createEmptyListingBlock() {
            var emptyListingMessage = document.createElement('P');
            emptyListingMessage.className = 'note-msg';
            emptyListingMessage.textContent = translate('There are no products matching the selection.');

            return emptyListingMessage;
        }

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
            var grid = document.querySelector('.products-grid');

            if (null === grid) {
                return;
            }

            var cells = Array.prototype.slice.call(grid.querySelectorAll('li')),
                colsPerRow = Math.floor(grid.clientWidth / cells[0].clientWidth);

            cells.map(function (cell, index) {
                cell.className = cell.className.replace(/\blast\b/ig, '');
                if (!((index + 1) % colsPerRow)) {
                    cell.className += ' ' + className;
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

        function createSortingSelect() {
            var sortingSelect = document.createElement('SELECT');

            if (typeof window.sortOrderConfig !== 'object' || 0 === window.sortOrderConfig.length) {
                return sortingSelect;
            }

            sortingSelect.addEventListener('change', function () {
                document.location.href = this.value
            }, true);

            window.sortOrderConfig.map(function (config) {
                sortingSelect.appendChild(createSortingSelectOption(config));
            });

            return sortingSelect;
        }

        function createSortingSelectOption(config) {
            var sortingOption = document.createElement('OPTION'),
                newUrl = url.updateQueryParameters({"order": config['code'], "dir": config['selectedDirection']});

            sortingOption.textContent = translate(config['code']);
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

        function createToolbar() {
            var toolbar = document.createElement('DIV');
            toolbar.className = 'toolbar roundedBorder';
            toolbar.appendChild(createTotalProductsNumberBlock());
            toolbar.appendChild(createSortingBlock());
            toolbar.appendChild(createProductsPerPageBlock());

            return toolbar;
        }

        function createTotalProductsNumberBlock() {
            var amount = document.createElement('P');
            amount.className = 'amount';
            amount.textContent = translate('%s Items(s)', totalNumberOfResults);

            return amount;
        }

        function createSortingBlock() {
            var sortBy = document.createElement('DIV'),
                sortByLabel = document.createElement('LABEL');
            sortBy.className = 'sort-by';
            sortByLabel.textContent = translate('Sort By');
            sortBy.appendChild(sortByLabel);
            sortBy.appendChild(createSortingSelect());

            return sortBy;
        }

        function createProductsPerPageBlock() {
            var productPerPage = document.createElement('DIV'),
                productPerPageLabel = document.createElement('LABEL');
            productPerPage.className = 'limiter';
            productPerPageLabel.textContent = translate('Items') + ': ';
            productPerPage.appendChild(productPerPageLabel);

            window.productsPerPage.map(function (numberOfProductsPerPage, index) {
                productPerPage.appendChild(createProductsPerPageElement(numberOfProductsPerPage));

                if (index < productsPerPage.length - 1) {
                    var separator = document.createTextNode(' | ');
                    productPerPage.appendChild(separator);
                }
            });

            return productPerPage;
        }

        function processGoogleTagManager() {
            window.dataLayer = [
                {
                    "google_tag_params": {
                        "ecomm_prodid" : "",
                        "ecomm_category" : "",
                        "ecomm_pagetype" : "category",
                        "ecomm_totalvalue" : ""
                    }
                },
                {
                    "cartItems" : magentoData.getCartItems()
                }
            ];

            (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
                new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
                j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
                '//www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
            })(window,document,'script','dataLayer','GTM-5F3F');
        }
    }
);
