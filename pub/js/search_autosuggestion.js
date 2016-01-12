define(['lib/ajax'], function (callAjax) {
    var autosuggestionBox = document.getElementById('search-autosuggestion'),
        searchInput = document.getElementById('search'),
        submitButton = searchInput.parentNode.querySelector('button'),
        minimalLength = 2;

    searchInput.addEventListener('keyup', function (event) {
        var value = event.target.value;

        submitButton.disabled = value.length === 0;

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
