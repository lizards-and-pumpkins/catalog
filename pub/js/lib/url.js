define({
    updateQueryParameter: function (parameterName, parameterValue) {
        var queryParameters = this.getQueryParameters(),
            urlWithoutQueryString = this.getUrlWithoutQueryString();

        queryParameters[parameterName] = parameterValue;

        return this.addQueryParametersToUrl(urlWithoutQueryString, queryParameters);
    },

    toggleQueryParameter: function (parameterName, parameterValue) {
        var queryParameters = this.getQueryParameters(),
            urlWithoutQueryString = this.getUrlWithoutQueryString();

        queryParameters = this.toggleQueryParameterValue(queryParameters, parameterName, parameterValue);

        return this.addQueryParametersToUrl(urlWithoutQueryString, queryParameters);
    },

    addQueryParametersToUrl: function (url, queryParameters) {
        var queryString = this.buildQueryString(queryParameters);

        if ('' === queryString) {
            return url;
        }

        return url + '?' + queryString;
    },

    toggleQueryParameterValue: function (queryParameters, parameterName, parameterValue) {
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
    },

    getQueryParameters: function () {
        var queryString = location.search.replace(/^\?/, '');

        if ('' === queryString) {
            return {};
        }

        return queryString.split('&').reduce(function (carry, item) {
            var keyValue = item.split('=');
            carry[keyValue[0]] = keyValue[1];
            return carry;
        }, {});
    },

    getQueryParameterValue: function (parameterName) {
        var queryParameters = this.getQueryParameters();

        if (!queryParameters.hasOwnProperty(parameterName)) {
            return null;
        }

        return queryParameters[parameterName];
    },

    getUrlWithoutQueryString: function () {
        return location.href.split('?')[0];
    },

    buildQueryString: function (parameters) {
        var pairs = [];

        for (var key in parameters) {
            if (parameters.hasOwnProperty(key)) {
                pairs.push(key + '=' + parameters[key]);
            }
        }

        return pairs.join('&');
    }
});
