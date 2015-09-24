define(['url', 'jquery'], function (url, $) {
    return {
        generatePagination: function (totalPageCount, paginationPlaceholderSelector) {
            var paginationPlaceholder = $(paginationPlaceholderSelector);

            if (!paginationPlaceholderSelector.length) {
                return;
            }

            var maxPaginationLinksToShow = 7,
                paginationQueryParameterName = 'p',
                pagination = $('<ol/>'),
                currentPageNumber = Math.max(1, url.getQueryParameterValue(paginationQueryParameterName));

            if (totalPageCount && 1 < currentPageNumber) {
                pagination.append(
                    $('<li/>').append(
                        $('<a/>').addClass('prev')
                            .prop('href', url.updateQueryParameter(paginationQueryParameterName, 1)).html('&#9664;')
                    )
                );
            }

            for (var pageNumber = 1; pageNumber <= totalPageCount; pageNumber++) {
                if (totalPageCount > maxPaginationLinksToShow && currentPageNumber < totalPageCount - 1) {
                    if (currentPageNumber === maxPaginationLinksToShow - 1) {
                        pagination.append(
                            $('<li/>').text('...')
                        );
                    }
                    continue;
                }
                if (currentPageNumber === pageNumber) {
                    pagination.append(
                        $('<li/>').addClass('current').text(pageNumber)
                    );
                } else {
                    pagination.append(
                        $('<li/>').append(
                            $('<a/>').prop('href', url.updateQueryParameter(paginationQueryParameterName, pageNumber))
                                .text(pageNumber)
                        )
                    );
                }
            }

            if (totalPageCount && totalPageCount > currentPageNumber) {
                pagination.append(
                    $('<li/>').append(
                        $('<a/>').addClass('next')
                            .prop('href', url.updateQueryParameter(paginationQueryParameterName, totalPageCount))
                            .html('&#9654;')
                    )
                );
            }

            paginationPlaceholder.append(pagination);
        }
    }
});
