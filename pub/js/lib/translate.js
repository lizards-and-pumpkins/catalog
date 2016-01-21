define(function() {
    function getTranslation(string) {
        if (typeof translations === 'undefined' || !translations.hasOwnProperty(string)) {
            return string;
        }

        return translations[string];
    }

    return function (string) {
        var translation = getTranslation(string);

        if (arguments.length === 1) {
            return translation;
        }

        return Array.prototype.slice.call(arguments).slice(1).reduce(function (carry, argument) {
            return carry.replace(/%s/, argument);
        }, translation);
    }
});
