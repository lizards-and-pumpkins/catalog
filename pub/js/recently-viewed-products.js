define(['local-storage', 'jquery'], function(storage) {
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

            if (!products.hasOwnProperty('length') || !products.length) {
                return '';
            }

            var productsList = jQuery('<ul/>').addClass('products-grid');

            for (var i = 0; i < products.length && productsList.length <= this.numProducts; i++) {
                if (currentProduct.hasOwnProperty('sku') && products[i]['sku'] == currentProduct['sku']) {
                    continue;
                }

                productsList.append(jQuery(products[i]['html']));
            }

            productsList.find('li').last().addClass('last');

            return jQuery('<div/>').append(productsList).html();
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
