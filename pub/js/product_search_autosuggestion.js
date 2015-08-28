define(['jquery'], function ($) {
    $(document).ready(function () {
        var autosuggestionBox = $('#search-autosuggestion'),
            minimalLength = 3;

        $('#search').bind('keyup', function () {
            var value = $(this).val().trim();

            if (value.length < minimalLength) {
                autosuggestionBox.html('');
                return;
            }

            /* TODO: Inject base URL */

            $.ajax({url: '/brera/catalogsearch/suggest?q=' + value}).done(function (data) {
                autosuggestionBox.html(data);
            });
        }).bind('blur', function() {
            autosuggestionBox.html('');
        });
    });
});
