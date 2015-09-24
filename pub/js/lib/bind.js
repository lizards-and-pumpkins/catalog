define(function () {
    return function (target, event, callback) {
        if (target.attachEvent) {
            target.attachEvent('on' + event, callback);
        } else {
            target.addEventListener(event, callback, false);
        }
    }
});
