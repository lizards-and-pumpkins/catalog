define(function() {
    return {

        get: function(key) {

            if (typeof localStorage == 'undefined') {
                return null;
            }

            return JSON.parse(localStorage.getItem(key));
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
