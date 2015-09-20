define({
    toggleQueryParameter: function (parameterName, parameterValue) {
        var queryParameters = this.getQueryParameters();

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

        var urlWithoutQueryString = this.getUrlWithoutQueryString(),
            queryString = this.buildQueryString(queryParameters);

        if ('' === queryString) {
            return urlWithoutQueryString;
        }

        return urlWithoutQueryString + '?' + queryString;
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
