define(function () {

    function getUrlWithoutQueryString() {
        return location.href.split('?')[0];
    }

    function buildQueryString(parameters) {
        var pairs = [];

        for (var key in parameters) {
            if (parameters.hasOwnProperty(key)) {
                pairs.push(key + '=' + parameters[key]);
            }
        }

        return pairs.join('&');
    }

    function getQueryParameters() {
        var queryString = location.search.replace(/^\?/, '');

        if ('' === queryString) {
            return {};
        }

        return queryString.split('&').reduce(function (carry, item) {
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
        updateQueryParameter: function (parameterName, parameterValue) {
            var queryParameters = getQueryParameters(),
                urlWithoutQueryString = getUrlWithoutQueryString();

            queryParameters[parameterName] = parameterValue;

            return addQueryParametersToUrl(urlWithoutQueryString, queryParameters);
        },

        toggleQueryParameter: function (parameterName, parameterValue) {
            var queryParameters = getQueryParameters(),
                urlWithoutQueryString = getUrlWithoutQueryString();

            queryParameters = toggleQueryParameterValue(queryParameters, parameterName, parameterValue);

            return addQueryParametersToUrl(urlWithoutQueryString, queryParameters);
        },

        getQueryParameterValue: function (parameterName) {
            var queryParameters = getQueryParameters();

            if (!queryParameters.hasOwnProperty(parameterName)) {
                return null;
            }

            return queryParameters[parameterName];
        }
    };
});
