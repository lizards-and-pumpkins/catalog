var brera = brera || {};

brera.recentlyViewedProducts = {

    storageKey: 'recently-viewed-products',
    numProducts: 4,

    addProductIntoLocalStorage: function(product) {

        if (typeof product == 'undefined') {
            return;
        }

        var recentlyViewedProducts = brera.localStorage.get(this.storageKey) || [];

        recentlyViewedProducts = this.removeProductFromListBySku(recentlyViewedProducts, product['sku']);
        recentlyViewedProducts.unshift(product);

        if (recentlyViewedProducts.length > this.numProducts + 1) {
            recentlyViewedProducts.shift();
        }

        brera.localStorage.set(this.storageKey, recentlyViewedProducts);
    },

    getRecentlyViewedProductsHtml: function(currentProductSku) {

        var products = brera.localStorage.get(this.storageKey);

        if (!products.length) {
            return '';
        }

        var productsList = jQuery('<ul/>').addClass('products-grid');

        for (var i = 0; i < products.length && productsList.length <= this.numProducts; i++) {
            if (products[i]['sku'] == currentProductSku) {
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
};
