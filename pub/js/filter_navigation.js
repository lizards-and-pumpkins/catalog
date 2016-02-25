define(['lib/url', 'pagination', 'lib/translate'], function (url, pagination, translate) {

    function getSelectedFilterValues(filterCode) {
        var rawSelectedValues = url.getQueryParameterValue(filterCode);

        if (null === rawSelectedValues) {
            return [];
        }

        return rawSelectedValues.split(',');
    }

    function capitalizeFirstLetter(string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
    }

    function toggleExpandedCollapsed(event) {
        if (event.target.className.match(/\bexpanded\b/ig)) {
            event.target.className = event.target.className.replace(/\bexpanded\b/ig, 'collapsed');
            event.target.nextSibling.style.display = 'none';
            return;
        }

        event.target.className = event.target.className.replace(/\bcollapsed\b/ig, 'expanded');
        event.target.nextSibling.style.display = 'block';
    }

    function scrollSelectedFilterOptionsIntoView(filterContainer) {
        if (domElementHasVerticalScrolling(filterContainer)) {
            var activeOption = filterContainer.querySelector('.active');

            if (null !== activeOption) {
                filterContainer.scrollTop = activeOption.offsetTop;
            }
        }
    }

    function domElementHasVerticalScrolling(domElement) {
        return domElement.scrollHeight > domElement.offsetHeight;
    }

    var FilterNavigation = {
        renderLayeredNavigation: function (filterNavigationJson, placeholderSelector) {
            if (typeof filterNavigationJson !== 'object') {
                return;
            }

            var filterNavigation = document.querySelector(placeholderSelector);

            if (null === filterNavigation) {
                return;
            }

            Object.keys(filterNavigationJson).map(function (filterCode) {
                if (0 === filterNavigationJson[filterCode].length) {
                    return;
                }

                var options = FilterNavigation[FilterNavigation.getFilterOptionBuilderName(filterCode)](
                    filterCode,
                    filterNavigationJson[filterCode]
                );

                var heading = document.createElement('DIV');
                heading.className = 'block-title roundedBorder expanded';
                heading.textContent = translate(filterCode);
                heading.addEventListener('click', toggleExpandedCollapsed, true);

                var filterContainer = document.createElement('DIV');
                filterContainer.className = 'filter-container';

                var optionList = document.createElement('OL');
                optionList.className = 'filter-content scroll-pane filter-' + filterCode;
                options.map(function (option) { optionList.appendChild(option) });

                filterNavigation.appendChild(heading);
                filterContainer.appendChild(optionList);
                filterNavigation.appendChild(filterContainer);

                scrollSelectedFilterOptionsIntoView(filterContainer);
            });
        },

        getFilterOptionBuilderName: function (filterCode) {
            var functionName = 'create' + capitalizeFirstLetter(filterCode) + 'FilterOptions';

            if (typeof this[functionName] === 'function') {
                return functionName;
            }

            return 'createDefaultFilterOptions';
        },

        createDefaultFilterOptions: function (filterCode, filterOptions) {
            var selectedFilterOptions = getSelectedFilterValues(filterCode);
            return filterOptions.reduce(function (carry, filterOption) {
                var option = document.createElement('LI'),
                    link = document.createElement('A'),
                    newUrl = url.toggleQueryParameter(filterCode, filterOption.value);

                link.textContent = filterOption.value + ' (' + filterOption.count + ')';
                link.href = url.removeQueryParameterFromUrl(newUrl, pagination.getPaginationQueryParameterName());
                option.appendChild(link);

                if (selectedFilterOptions.indexOf(filterOption.value) !== -1) {
                    option.className = 'active';
                }

                carry.push(option);
                return carry;
            }, []);
        },

        createColorFilterOptions: function (filterCode, filterOptions) {
            var selectedColors = getSelectedFilterValues(filterCode);
            return filterOptions.reduce(function (carry, filterOption) {
                var option = document.createElement('LI'),
                    link = document.createElement('A'),
                    newUrl = url.toggleQueryParameter(filterCode, filterOption.value.toString());

                link.innerHTML = selectedColors.indexOf(filterOption.value.toString()) !== -1 ? '&#x2713;' : '&nbsp;';
                link.style.backgroundColor = '#' + filterOption.value;
                link.href = url.removeQueryParameterFromUrl(newUrl, pagination.getPaginationQueryParameterName());
                option.appendChild(link);

                carry.push(option);
                return carry;
            }, []);
        },

        createPriceFilterOptions: function (filterCode, filterOptions) {
            var selectedFilterOptions = getSelectedFilterValues(filterCode);
            return filterOptions.reduce(function (carry, filterOption) {
                if (0 === filterOption.count) {
                    return carry;
                }

                var ranges = filterOption.value.match(/(\d+,\d+)/g),
                    parameterValue = ranges.join('-').replace(/,/g, '.'),
                    option = document.createElement('LI'),
                    link = document.createElement('A'),
                    newUrl = url.toggleQueryParameter(filterCode, parameterValue);

                link.textContent = filterOption.value + ' (' + filterOption.count + ')';
                link.href = url.removeQueryParameterFromUrl(newUrl, pagination.getPaginationQueryParameterName());
                option.appendChild(link);

                if (selectedFilterOptions.indexOf(parameterValue) !== -1) {
                    option.className = 'active';
                }

                carry.push(option);
                return carry;
            }, []);
        }
    };

    return FilterNavigation;
});
