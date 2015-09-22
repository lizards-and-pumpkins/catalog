require(['jquery', 'filter_navigation', 'pagination', 'common'], function ($, filterNavigation, pagination) {
    $(document).ready(function () {
        filterNavigation.generateLayeredNavigation(filterNavigationJson, '#filter-navigation');
        pagination.generatePagination(totalPagesCount, '#pagination');
    });
});
