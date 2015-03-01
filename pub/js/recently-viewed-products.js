var brera = brera || {};

brera.localStorage = {

    get: function(key) {

        if (typeof localStorage == 'undefined') {
            return null;
        }

        return JSON.parse(localStorage.getItem(key));
    },

    set: function(key, value) {

        try {
            localStorage.setItem(key, JSON.stringify(value));
        } catch (e) {
            /* Some browsers are not allowing local storage access in private mode. */
        }
    }
};

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

jQuery(document).ready(function() {

    if (typeof product != 'undefined') {
        var recentlyViewedProductsListHtml = brera.recentlyViewedProducts.getRecentlyViewedProductsHtml(product['sku']);

        if (recentlyViewedProductsListHtml) {
            jQuery('#recently-viewed-products').find('.swipe-container').eq(0)
                .html(recentlyViewedProductsListHtml)
                .show();
        }

        brera.recentlyViewedProducts.addProductIntoLocalStorage(product);
    }
});
