define(['lib/ajax', 'product_grid'], function (callAjax, productGrid) {
    
    return function (productId) {
        callAjax(baseUrl + 'api/products/' + productId + '/relations/related-models', function (responseText) {
            productGrid.renderGrid(JSON.parse(responseText).data, '#all-models');
        }, 'application/vnd.lizards-and-pumpkins.product_relations.v1+json');
    }
});
