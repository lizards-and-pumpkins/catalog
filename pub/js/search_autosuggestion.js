define(['lib/bind'], function (bind) {

    function callAjax(url, callback) {
        var xmlhttp = new XMLHttpRequest;
        xmlhttp.onreadystatechange = function () {
            if (4 === xmlhttp.readyState && 200 === xmlhttp.status) {
                callback(xmlhttp.responseText);
            }
        };
        xmlhttp.open('GET', url, true);
        xmlhttp.send();
    }

    var autosuggestionBox = document.querySelector('#search-autosuggestion'),
        searchInput = document.querySelector('#search'),
        minimalLength = 3;

    bind(searchInput, 'keyup', function (event) {
        var value = event.target.value;

        if (value.length < minimalLength) {
            autosuggestionBox.innerHTML = '';
            return;
        }

        /* TODO: Inject base URL */

        callAjax('/lizards-and-pumpkins/catalogsearch/suggest?q=' + value, function (responseText) {
            autosuggestionBox.innerHTML = responseText;
        });
    });

    bind(searchInput, 'blur', function () {
//        autosuggestionBox.innerHTML = '';
    });
});
