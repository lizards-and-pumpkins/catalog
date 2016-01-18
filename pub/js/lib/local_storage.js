define(function() {

    function isJson(str) {
        try {
            JSON.parse(str);
        } catch (e) {
            return false;
        }
        return true;
    }

    return {

        get: function(key) {

            if (typeof localStorage == 'undefined') {
                return null;
            }

            var item = localStorage.getItem(key);

            if (!isJson(item)) {
                return null;
            }

            return JSON.parse(item);
        },

        set: function(key, value) {

            try {
                localStorage.setItem(key, JSON.stringify(value));
            } catch (e) {
                /* Some browsers are not allowing local storage access in private mode. */
            }
        }
    }
});
