define(['lib/ajax'], function (callAjax) {
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
