define(['../../pub/js/pagination'], function (Pagination) {
    describe('Pagination', function () {
        it('is empty if it only has one page', function () {
            var totalNumberOfResults = 20,
                productsPerPage = [{number: 20, selected: true}],
                pagination = Pagination.renderPagination(totalNumberOfResults, productsPerPage);
            expect(pagination.tagName).toBe('DIV');
            expect(pagination.id).toBe('pagination');
        });
    });
});
