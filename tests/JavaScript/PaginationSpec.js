define(['../../pub/js/pagination'], function (Pagination) {
    var placeholderId = 'placeholder-id',
        placeholderSelector = '#' + placeholderId;

    function createTemporaryElement() {
        var placeholder = document.createElement('DIV');
        placeholder.id = placeholderId;
        document.getElementsByTagName('BODY')[0].appendChild(placeholder);
    }

    function removeTemporaryElement() {
        document.getElementsByTagName('BODY')[0].removeChild(document.getElementById(placeholderId));
    }

    describe('Pagination', function () {
        beforeEach(function () {
            createTemporaryElement();
        });

        afterEach(function () {
            removeTemporaryElement();
        });

        it('is not rendered if non existing container selector is specified', function () {
            var documentBodyHtmlBefore = document.body.innerHTML,
                totalNumberOfResults = 100,
                productsPerPage = [{number: 1, selected: true}];

            Pagination.renderPagination(totalNumberOfResults, productsPerPage, 'non-existing-selector');
            expect(document.body.innerHTML).toBe(documentBodyHtmlBefore);
        });

        it('is not rendered if it only has one page', function () {
            var documentBodyHtmlBefore = document.body.innerHTML,
                totalNumberOfResults = 20,
                productsPerPage = [{number: 20, selected: true}];

            Pagination.renderPagination(totalNumberOfResults, productsPerPage, placeholderSelector);
            expect(document.body.innerHTML).toBe(documentBodyHtmlBefore);
        });
    });
});
