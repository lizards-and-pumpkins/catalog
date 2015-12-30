define(function () {
    return function callAjax(url, callback, acceptHeaderValue) {
        var xmlhttp = new XMLHttpRequest;
        xmlhttp.onreadystatechange = function () {
            if (4 === xmlhttp.readyState && 200 === xmlhttp.status) {
                callback(xmlhttp.responseText);
            }
        };
        xmlhttp.open('GET', url, true);
        if (acceptHeaderValue) {
            xmlhttp.setRequestHeader('Accept', acceptHeaderValue);
        }
        xmlhttp.send();
    }
});
