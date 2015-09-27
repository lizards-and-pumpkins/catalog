define(['lib/url'], function (url) {
    var paginationQueryParameterName = 'p',
        maxPaginationLinksToShow = 7;

    var createPreviousPaginationItem = function () {
        var item = document.createElement('LI'),
            link = document.createElement('A');
        link.className = 'prev';
        link.href = url.updateQueryParameter(paginationQueryParameterName, 1);
        link.innerHTML = '&#9664;';
        item.appendChild(link);

        return item;
    };

    var createNextPaginationItem = function (lastPageNumber) {
        var item = document.createElement('LI'),
            link = document.createElement('A');
        link.className = 'next';
        link.href = url.updateQueryParameter(paginationQueryParameterName, lastPageNumber);
        link.innerHTML = '&#9654;';
        item.appendChild(link);

        return item;
    };

    return {
        generatePagination: function (totalPageCount, paginationPlaceholderSelector) {
            var paginationPlaceholder = document.querySelector(paginationPlaceholderSelector);

            if (null === paginationPlaceholderSelector) {
                return;
            }

            var pagination = document.createElement('OL'),
                currentPageNumber = Math.max(1, url.getQueryParameterValue(paginationQueryParameterName));

            if (totalPageCount && 1 < currentPageNumber) {
                pagination.appendChild(createPreviousPaginationItem());
            }

            for (var pageNumber = 1; pageNumber <= totalPageCount; pageNumber++) {
                var paginationItem = document.createElement('LI');
                if (totalPageCount > maxPaginationLinksToShow && currentPageNumber < totalPageCount - 1) {
                    if (currentPageNumber === maxPaginationLinksToShow - 1) {
                        paginationItem.textContent = '...';
                        pagination.appendChild(paginationItem);
                    }
                    continue;
                }
                if (currentPageNumber === pageNumber) {
                    paginationItem.className = 'current';
                    paginationItem.textContent = pageNumber.toString();
                } else {
                    var paginationLink = document.createElement('A');
                    paginationLink.textContent = pageNumber.toString();
                    paginationLink.href = url.updateQueryParameter(paginationQueryParameterName, pageNumber);
                    paginationItem.appendChild(paginationLink);
                }
                pagination.appendChild(paginationItem);
            }

            if (totalPageCount && totalPageCount > currentPageNumber) {
                pagination.appendChild(createNextPaginationItem(totalPageCount));
            }

            paginationPlaceholder.appendChild(pagination);
        }
    }
});
