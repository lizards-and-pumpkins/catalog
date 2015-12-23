define(function () {

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

    Array.prototype.map.call(document.querySelectorAll('.search-form'), function (searchForm) {
        var autosuggestionBox = searchForm.querySelector('#search-autosuggestion'),
            searchInput = searchForm.querySelector('#search'),
            minimalLength = 3;

        searchInput.addEventListener('keyup', function (event) {
            var value = event.target.value;

            if (value.length < minimalLength) {
                autosuggestionBox.innerHTML = '';
                return;
            }

            callAjax(baseUrl + 'catalogsearch/suggest?q=' + value, function (responseText) {
                autosuggestionBox.innerHTML = responseText;
            });
        }, true);

        searchInput.addEventListener('blur', function () {
//        autosuggestionBox.innerHTML = '';
        }, true);
    });
});
