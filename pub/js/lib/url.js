define(function () {

    function getUrlWithoutQueryString(url) {
        return url.split('?')[0];
    }

    function buildQueryString(parameters) {
        return Object.keys(parameters).map(function (key) {
            return encodeURIComponent(key) + '=' + encodeURIComponent(parameters[key]);
        }).join('&');
    }

    function getQueryParameters(url) {
        var urlParts = url.split('?');

        if (urlParts.length < 2 || '' === urlParts[1]) {
            return {};
        }

        return decodeURIComponent(urlParts[1]).split('&').reduce(function (carry, item) {
            var keyValue = item.split('=');
            carry[keyValue[0]] = decodeURI(keyValue[1]);
            return carry;
        }, {});
    }

    function toggleQueryParameterValue(queryParameters, parameterName, parameterValue) {
        if (undefined === queryParameters[parameterName]) {
            queryParameters[parameterName] = parameterValue;
        } else {
            var values = queryParameters[parameterName].split(','),
                needleIndex = values.indexOf(parameterValue);
            if (-1 === needleIndex) {
                values.push(parameterValue);
            } else {
                values.splice(needleIndex, 1);
            }

            if (values.length) {
                queryParameters[parameterName] = values.join();
            } else {
                delete queryParameters[parameterName];
            }
        }

        return queryParameters;
    }

    function addQueryParametersToUrl(url, queryParameters) {
        var queryString = buildQueryString(queryParameters);

        if ('' === queryString) {
            return url;
        }

        return url + '?' + queryString;
    }

    return {
        updateQueryParameters: function (parameters) {
            var queryParameters = getQueryParameters(location.href),
                urlWithoutQueryString = getUrlWithoutQueryString(location.href);

            Object.keys(parameters).map(function (parameterName) {
                queryParameters[parameterName] = parameters[parameterName];
            });

            return addQueryParametersToUrl(urlWithoutQueryString, queryParameters);
        },

        updateQueryParameter: function (parameterName, parameterValue) {
            var queryParameters = getQueryParameters(location.href),
                urlWithoutQueryString = getUrlWithoutQueryString(location.href);

            queryParameters[parameterName] = parameterValue;

            return addQueryParametersToUrl(urlWithoutQueryString, queryParameters);
        },

        toggleQueryParameter: function (parameterName, parameterValue) {
            var queryParameters = getQueryParameters(location.href),
                urlWithoutQueryString = getUrlWithoutQueryString(location.href);

            queryParameters = toggleQueryParameterValue(queryParameters, parameterName, parameterValue);

            return addQueryParametersToUrl(urlWithoutQueryString, queryParameters);
        },

        getQueryParameterValue: function (parameterName) {
            var queryParameters = getQueryParameters(location.href);

            if (!queryParameters.hasOwnProperty(parameterName)) {
                return null;
            }

            return queryParameters[parameterName];
        },

        removeQueryParameterFromUrl: function (url, parameterName) {
            var queryParameters = getQueryParameters(url);
            delete queryParameters[parameterName];

            return getUrlWithoutQueryString(url) + '?' + buildQueryString(queryParameters);
        }
    };
});
