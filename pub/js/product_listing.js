require(
    ['jquery', 'product_grid', 'filter_navigation', 'pagination', 'common'],
    function ($, productGrid, filterNavigation, pagination) {
        $(document).ready(function () {
            productGrid.renderGrid(productListingJson, '#products-grid-container');
            filterNavigation.generateLayeredNavigation(filterNavigationJson, '#filter-navigation');
            pagination.generatePagination(totalPagesCount, '#pagination');
        });
    }
);
