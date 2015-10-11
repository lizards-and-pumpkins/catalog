require(
    ['lib/domReady', 'product_grid', 'filter_navigation', 'pagination', 'common'],
    function (domReady, productGrid, filterNavigation, pagination) {
        domReady(function () {
            productGrid.renderGrid(productListingJson, '#products-grid-container');
            filterNavigation.generateLayeredNavigation(filterNavigationJson, '#filter-navigation');
            pagination.generatePagination(totalPagesCount, '#pagination');
        });
    }
);
