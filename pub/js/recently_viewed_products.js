define(['lib/local_storage'], function(storage) {
    return {

        storageKey: 'recently-viewed-products',
        numProducts: 4,

        addProductIntoLocalStorage: function(product) {

            if (typeof product == 'undefined') {
                return;
            }

            var recentlyViewedProducts = storage.get(this.storageKey) || [];

            recentlyViewedProducts = this.removeProductFromListBySku(recentlyViewedProducts, product['sku']);
            recentlyViewedProducts.unshift(product);

            if (recentlyViewedProducts.length > this.numProducts + 1) {
                recentlyViewedProducts.shift();
            }

            storage.set(this.storageKey, recentlyViewedProducts);
        },

        getRecentlyViewedProductsHtml: function(currentProduct) {

            var products = storage.get(this.storageKey);

            if (null === products || !products.hasOwnProperty('length') || !products.length) {
                return '';
            }

            var productList = document.createElement('UL');
            productList.className = 'product-grid';

            for (var i = 0; i < products.length && i < this.numProducts; i++) {
                if (currentProduct.hasOwnProperty('sku') && products[i]['sku'] == currentProduct['sku']) {
                    continue;
                }

                productList.innerHTML += products[i]['html'];
            }

            // TODO: Add "last" class to last element of the list.

            var temporaryContainer = document.createElement('DIV');
            temporaryContainer.appendChild(productList);

            return temporaryContainer.innerHTML;
        },

        removeProductFromListBySku: function(list, sku) {

            var newList = [];

            for (var i = 0; i < list.length; i++) {
                if (list[i]['sku'] != sku) {
                    newList.push(list[i]);
                }
            }

            return newList
        }
    }
});
