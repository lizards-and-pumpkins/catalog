define(['lib/ajax'], function (callAjax) {
    var allModelsBox = document.getElementById('all-models');
    
    return function (productId) {
        callAjax(baseUrl + 'api/products/' + productId + '/relations/related-models', function (responseText) {
            allModelsBox.innerHTML = responseText;
        }, 'application/vnd.lizards-and-pumpkins.product_relations.v1+json');
    }
});
