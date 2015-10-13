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

            filterNavigationJson.map(function (filter) {
                var attributeCode = Object.keys(filter).shift();
                var options = FilterNavigation[FilterNavigation.getFilterOptionBuilderName(attributeCode)](
                    attributeCode,
                    filter[attributeCode]
                );

                var heading = document.createElement('DIV');
                heading.className = 'block-title roundedBorder expanded';
                heading.textContent = attributeCode; // TODO: Translate

                var optionList = document.createElement('OL');
                optionList.className = 'filter-content scroll-pane filter-' + attributeCode;
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
            return Object.keys(filterOptions).reduce(function (carry, value) {
                var option = document.createElement('LI'),
                    link = document.createElement('A');
                link.textContent = value + ' (' + filterOptions[value] + ')';
                link.href = url.toggleQueryParameter(filterCode, value);
                option.appendChild(link);

                if (selectedFilterOptions.indexOf(value) !== -1) {
                    option.className = 'active';
                }

                carry.push(option);
                return carry;
            }, []);
        },

        createColorFilterOptions: function (filterCode, filterOptions) {
            var selectedColors = this.getSelectedFilterValues(filterCode);
            return Object.keys(filterOptions).reduce(function (carry, value) {
                var option = document.createElement('LI'),
                    link = document.createElement('A');
                link.innerHTML = selectedColors.indexOf(value.toString()) !== -1 ? '&#x2713;' : '&nbsp;';
                link.style.backgroundColor = '#' + value;
                link.href = url.toggleQueryParameter(filterCode, value.toString());
                option.appendChild(link);

                carry.push(option);
                return carry;
            }, []);
        },

        createPriceFilterOptions: function (filterCode, filterOptions) {
            var priceStep = 20,
                selectedPriceRanges = this.getSelectedFilterValues(filterCode),
                priceRanges = Object.keys(filterOptions).reduce(function (carry, value) {
                    var rangeNumber = Math.floor(value / priceStep);
                    if (typeof carry[rangeNumber] === 'undefined') {
                        carry[rangeNumber] = 0;
                    }
                    carry[rangeNumber] += filterOptions[value];
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
