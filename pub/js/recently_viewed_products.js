define(['lib/local_storage'], function (storage) {

    var storageKey = 'recently-viewed-products',
        numProducts = 4;

    function removeProductFromListBySku(list, sku) {
        return list.filter(function (item) {
            return item['product_id'] !== sku;
        });
    }

    return {
        addProductIntoLocalStorage: function (productData) {
            var recentlyViewedProducts = storage.get(storageKey) || [];

            recentlyViewedProducts = removeProductFromListBySku(recentlyViewedProducts, productData['product_id']);
            recentlyViewedProducts.unshift(productData);

            if (recentlyViewedProducts.length > numProducts + 1) {
                recentlyViewedProducts.pop();
            }

            storage.set(storageKey, recentlyViewedProducts);
        },

        getRecentlyViewedProductsExceptCurrent: function (currentProduct) {
            var products = storage.get(storageKey) || [];

            return products.filter(function (product) {
                return currentProduct.getSku() !== product['product_id'];
            });
        }
    }
});
