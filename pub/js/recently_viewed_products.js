define(['lib/local_storage'], function(storage) {

    var storageKey = 'recently-viewed-products',
        numProducts = 4;

    function removeProductFromListBySku(list, sku) {
        return list.filter(function (item) {
            return item['sku'] !== sku;
        });
    }

    return {

        addProductIntoLocalStorage: function(product) {

            if (typeof product == 'undefined') {
                return;
            }

            var recentlyViewedProducts = storage.get(storageKey) || [];

            recentlyViewedProducts = removeProductFromListBySku(recentlyViewedProducts, product['sku']);
            recentlyViewedProducts.unshift(product);

            if (recentlyViewedProducts.length > numProducts + 1) {
                recentlyViewedProducts.shift();
            }

            storage.set(storageKey, recentlyViewedProducts);
        },

        getRecentlyViewedProductsHtml: function(currentProduct) {

            var products = storage.get(storageKey) || [];

            var liHtml = products.reduce(function (carry, product) {
                if (currentProduct.hasOwnProperty('sku') && product['sku'] !== currentProduct['sku']) {
                    carry += product['html'];
                }
                return carry;
            }, '');

            // TODO: Add "last" class to last element of the list.

            return '<ul class="products-grid">' + liHtml + '</ul>';
        }
    }
});
