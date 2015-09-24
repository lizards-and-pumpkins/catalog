require(
    ['lib/domReady', 'filter_navigation', 'pagination', 'common'],
    function (domReady, filterNavigation, pagination) {
        domReady(function () {
            filterNavigation.generateLayeredNavigation(filterNavigationJson, '#filter-navigation');
            pagination.generatePagination(totalPagesCount, '#pagination');
        });
    }
);
