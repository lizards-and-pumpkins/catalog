define(['lib/local_storage'], function (storage) {

    var storageKey = 'recently-viewed-products',
        numProducts = 4;

    function removeProductFromListBySku(list, sku) {
        return list.filter(function (item) {
            return item['sku'] !== sku;
        });
    }

    return {

        addProductIntoLocalStorage: function (product) {

            if (typeof product == 'undefined') {
                return;
            }

            var recentlyViewedProducts = storage.get(storageKey) || [];

            recentlyViewedProducts = removeProductFromListBySku(recentlyViewedProducts, product['sku']);
            recentlyViewedProducts.unshift(product);

            if (recentlyViewedProducts.length > numProducts + 1) {
                recentlyViewedProducts.pop();
            }

            storage.set(storageKey, recentlyViewedProducts);
        },

        getRecentlyViewedProductsHtml: function (currentProduct) {

            var products = storage.get(storageKey) || [];

            var liHtml = products.reduce(function (carry, product, index) {
                if (currentProduct.getSku() !== product['sku']) {
                    var elementHtml = product['html'];
                    if (index === products.length - 1) {
                        elementHtml = elementHtml.replace(/class="item"/igm, 'class="item last"');
                    }
                    carry += elementHtml;
                }
                return carry;
            }, '');

            return '<ul class="products-grid">' + liHtml + '</ul>';
        }
    }
});
