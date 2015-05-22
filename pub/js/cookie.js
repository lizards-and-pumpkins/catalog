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
            var jsonData = JSON.parse(decodeURIComponent(this.get(cookieKey)));

            if (null === jsonData || !jsonData.hasOwnProperty(jsonKey)) {
                return '';
            }

            return jsonData[jsonKey];
        }
    }
});
