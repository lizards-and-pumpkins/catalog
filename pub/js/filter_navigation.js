define(['lib/url'], function (url) {

    var FilterNavigation = {
        generateLayeredNavigation: function (filterNavigationJson, filterNavigationPlaceholderSelector) {
            if (typeof filterNavigationJson !== 'object') {
                return;
            }

            var filterNavigation = document.querySelector(filterNavigationPlaceholderSelector);

            if (null === filterNavigation) {
                return;
            }

            Object.keys(filterNavigationJson).map(function (filterCode) {
                var options = FilterNavigation[FilterNavigation.getFilterOptionBuilderName(filterCode)](
                    filterCode,
                    filterNavigationJson[filterCode]
                );

                var heading = document.createElement('DIV');
                heading.className = 'block-title roundedBorder expanded';
                heading.textContent = filterCode;

                var optionList = document.createElement('OL');
                optionList.className = 'filter-content scroll-pane filter-' + filterCode;
                options.map(function (option) { optionList.appendChild(option) });

                filterNavigation.appendChild(heading);
                filterNavigation.appendChild(optionList);
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
                var option = document.createElement('LI'),
                    link = document.createElement('A');
                link.textContent = filterOption.value + ' (' + filterOption.count + ')';
                link.href = url.toggleQueryParameter(filterCode, filterOption.value);
                option.appendChild(link);

                if (selectedFilterOptions.indexOf(filterOption.value) !== -1) {
                    option.className = 'active';
                }

                carry.push(option);
                return carry;
            }, []);
        },

        createColorFilterOptions: function (filterCode, filterOptions) {
            var selectedColors = this.getSelectedFilterValues(filterCode);
            return filterOptions.reduce(function (carry, filterOption) {
                var option = document.createElement('LI'),
                    link = document.createElement('A');
                link.innerHTML = selectedColors.indexOf(filterOption.value.toString()) !== -1 ? '&#x2713;' : '&nbsp;';
                link.style.backgroundColor = '#' + filterOption.value;
                link.href = url.toggleQueryParameter(filterCode, filterOption.value.toString());
                option.appendChild(link);

                carry.push(option);
                return carry;
            }, []);
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
                options = [];

            for (var rangeNumber in priceRanges) {
                if (!priceRanges.hasOwnProperty(rangeNumber)) {
                    continue;
                }

                var priceFrom = rangeNumber * priceStep,
                    priceTo = (parseInt(rangeNumber) + 1) * parseInt(priceStep) - 0.01,
                    priceRangeString = priceFrom + '~' + priceTo,
                    option = document.createElement('LI'),
                    link = document.createElement('A');
                link.href = url.toggleQueryParameter(filterCode, priceRangeString);
                link.innerHTML = priceFrom + ' &euro; - ' + priceTo + ' &euro; (' + priceRanges[rangeNumber] + ')';
                option.appendChild(link);

                if (selectedPriceRanges.indexOf(priceRangeString) !== -1) {
                    option.className = 'active';
                }

                options.push(option);
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
