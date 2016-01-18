define(['../../pub/js/pagination'], function (Pagination) {
    describe('Pagination', function () {
        it('is not rendered if non existing container selector is specified', function () {
            var documentBefore = document;
            Pagination.renderPagination(100, 1, 'non-existing-selector');
            expect(document).toBe(documentBefore);
        });
    });
});
