define(['lib/ajax', 'product_grid', 'lib/swiping_container'], function (callAjax, productGrid, initializeSwiping) {
    
    return function (productId) {
        callAjax(baseUrl + 'api/products/' + productId + '/relations/related-models', function (responseText) {
            var productGridJson = JSON.parse(responseText).data;

            if (productGridJson.length > 0) {
                var heading = document.querySelector('.all-models h2');
                heading.style.display = 'block';

                productGrid.renderGrid(productGridJson, '#all-models');
                initializeSwiping('#all-models', 'ul');
            }

        }, 'application/vnd.lizards-and-pumpkins.product_relations.v1+json');
    }
});
