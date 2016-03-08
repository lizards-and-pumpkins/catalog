define(['lib/cookie'], function (cookie) {
    return {
        getCartItems: function () {
            var cartItems = cookie.getJsonValue('lizardsAndPumpkinsTransport', 'cartItems');

            if (!Array.isArray(cartItems)) {
                return [];
            }

            return cartItems;
        },

        getCartTotal: function () {
            var cartTotal = cookie.getJsonValue('lizardsAndPumpkinsTransport', 'cartTotal');

            if ('' === cartTotal) {
                return '0,00 €';
            }

            return cartTotal;
        },

        isCustomerLoggedIn: function () {
            return cookie.getJsonValue('lizardsAndPumpkinsTransport', 'isCustomerLoggedIn') === 1;
        }
    };
});
