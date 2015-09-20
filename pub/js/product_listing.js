require(['jquery', 'filter_navigation', 'common'], function ($, filterNavigation) {
    $(document).ready(function () {
        filterNavigation.generateLayeredNavigation();
    });
});
