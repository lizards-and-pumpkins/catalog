define(['url', 'jquery'], function (url, $) {

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
                var options = FilterNavigation[FilterNavigation.getFilterOptionBuilderName(filter.code)](
                    filter.code,
                    filter.options
                );

                filterNavigation.append(
                    $('<div/>').addClass('block-title roundedBorder expanded').text(filter.label)
                ).append(
                    $('<ol/>').addClass('filter-content scroll-pane filter-' + filter.code).append(options)
                );
            });
        },

        getFilterOptionBuilderName: function (filterCode) {
            var functionName = 'create' + this.capitalizeFirstLetter(filterCode) + 'FilterOptions';

            if (typeof this[functionName] === 'function') {
                return functionName;
            }

            return 'createDefaultFilterOptions';
        },

        createDefaultFilterOptions: function (filterCode, filterOptions) {
            var selectedFilterOptions = this.getSelectedFilterValues(filterCode);
            return filterOptions.reduce(function (carry, filterOption) {
                var option = $('<li/>').append(
                    $('<a/>').text(filterOption.value + ' (' + filterOption.count + ')')
                        .prop('href', url.toggleQueryParameter(filterCode, filterOption.value))
                );

                if (-1 < selectedFilterOptions.indexOf(filterOption.value)) {
                    option.addClass('active');
                }

                return carry.add(option);
            }, $());
        },

        createColorFilterOptions: function (filterCode, filterOptions) {
            var selectedColors = this.getSelectedFilterValues(filterCode);
            return filterOptions.reduce(function (carry, filterOption) {
                return carry.add(
                    $('<li/>').append(
                        $('<a/>').html(-1 < selectedColors.indexOf(filterOption.value.toString()) ? '&#x2713;' : '&nbsp;')
                            .css('background-color', '#' + filterOption.value)
                            .prop('href', url.toggleQueryParameter(filterCode, filterOption.value.toString()))
                    )
                );
            }, $());
        },

        createPriceFilterOptions: function (filterCode, filterOptions) {
            var priceStep = 20,
                selectedPriceRanges = this.getSelectedFilterValues(filterCode),
                priceRanges = filterOptions.reduce(function (carry, filterOption) {
                    var rangeNumber = Math.floor(filterOption.value / priceStep);
                    if (typeof carry[rangeNumber] === 'undefined') {
                        carry[rangeNumber] = 0;
                    }
                    carry[rangeNumber] += filterOption.count;
                    return carry;
                }, {}),
                options = $();

            for (var rangeNumber in priceRanges) {
                if (!priceRanges.hasOwnProperty(rangeNumber)) {
                    continue;
                }

                var priceFrom = rangeNumber * priceStep,
                    priceTo = (parseInt(rangeNumber) + 1) * parseInt(priceStep) - 0.01,
                    priceRangeString = priceFrom + '~' + priceTo,
                    option = $('<li/>').append(
                        $('<a/>').prop('href', url.toggleQueryParameter(filterCode, priceRangeString))
                            .html(priceFrom + ' &euro; - ' + priceTo + ' &euro; (' + priceRanges[rangeNumber] + ')')
                    );

                if (-1 < selectedPriceRanges.indexOf(priceRangeString)) {
                    option.addClass('active');
                }

                options = options.add(option);
            }

            return options;
        },

        getSelectedFilterValues: function(filterCode) {
            var rawSelectedValues = url.getQueryParameterValue(filterCode);

            if (null === rawSelectedValues) {
                return [];
            }

            return rawSelectedValues.split(',');
        },

        capitalizeFirstLetter: function (string) {
            return string.charAt(0).toUpperCase() + string.slice(1);
        }
    };

    return FilterNavigation;
});
