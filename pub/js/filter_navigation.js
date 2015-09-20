define(['url', 'jquery'], function(url, $) {

    var FilterNavigation = {
        generateLayeredNavigation: function (filterNavigationJson, filterNavigationPlaceholderSelector) {
            if (typeof filterNavigationJson !== 'object') {
                return;
            }

            var filterNavigation = $(filterNavigationPlaceholderSelector);

            if (!filterNavigation.length) {
                return;
            }

            filterNavigationJson.map(function (filter) {
                var optionElements = filter.options.reduce(function (carry, filterOption) {
                    return carry.add(FilterNavigation.createFilterOption(filter.code, filterOption));
                }, $());

                filterNavigation.append(
                    $('<div/>').addClass('block-title roundedBorder expanded').text(filter.label)
                ).append(
                    $('<ol/>').addClass('filter-content scroll-pane filter-' + filter.code).append(optionElements)
                );
            });
        },

        createFilterOption: function (filterCode, filterOption) {
            return this[this.getFilterOptionBuilderName(filterCode)].call(this, filterCode, filterOption);
        },

        getFilterOptionBuilderName: function(filterCode) {
            var functionName = 'create' + this.capitalizeFirstLetter(filterCode) + 'FilterOption';

            if (typeof this[functionName] === 'function') {
                return functionName;
            }

            return 'createDefaultFilterOption';
        },

        createDefaultFilterOption: function (filterCode, filterOption) {
            var option = $('<li/>').append(
                $('<a/>').text(filterOption.value + ' (' + filterOption.count + ')')
                    .prop('href', url.toggleQueryParameter(filterCode, filterOption.value))
            );

            if (true === filterOption.is_selected) {
                option.addClass('active');
            }

            return option;
        },

        createColorFilterOption: function (filterCode, filterOption) {
            return $('<li/>').append(
                $('<a/>').html(true === filterOption.is_selected ? '&#x2713;' : '&nbsp;')
                    .css('background-color', '#' + filterOption.value)
                    .prop('href', url.toggleQueryParameter(filterCode, filterOption.value))
            );
        },

        capitalizeFirstLetter: function (string) {
            return string.charAt(0).toUpperCase() + string.slice(1);
        }
    };

    return FilterNavigation;
});
