define(function() {
    return {
        get: function(key) {
            var value = '; ' + document.cookie;
            var parts = value.split('; ' + key + '=');

            if (parts.length == 2) {
                return parts.pop().split(';').shift();
            }

            return null;
        },
        getJsonValue: function(cookieKey, jsonKey) {
            var jsonData = JSON.parse(unescape(this.get(cookieKey)));

            return jsonData[jsonKey];
        }
    }
});
