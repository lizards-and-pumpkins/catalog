define(['../../pub/js/magento_data'], function (MagentoData) {

    function getTransportCookieString() {
        var value = '; ' + document.cookie;
        var parts = value.split('; lizardsAndPumpkinsTransport=');

        if (2 === parts.length) {
            return parts.pop().split(';').shift();
        }

        return null;
    }

    function setTransportCookie(cookieValue) {
        var date = new Date();
        date.setTime(date.getTime() + 60 * 1000);

        var expiryDate = date.toUTCString(),
            value = JSON.stringify(cookieValue);

        document.cookie = 'lizardsAndPumpkinsTransport=' + value + '; expires=' + expiryDate + '; path=/';
    }

    function removeTransportCookie() {
        var date = new Date();
        date.setTime(date.getTime() - 1);

        var expiryDate = date.toUTCString();

        document.cookie = 'lizardsAndPumpkinsTransport=; expires=' + expiryDate + '; path=/';
    }

    describe('Magento Data', function () {
        var originalCookieValue;

        beforeEach(function () {
            originalCookieValue = getTransportCookieString();
        });

        afterEach(function () {
            if (null === originalCookieValue) {
                removeTransportCookie();
                return;
            }

            document.cookie = originalCookieValue;
        });

        it('cart items are empty array if transport cookie value is not set', function () {
            expect(MagentoData.getCartItems()).toEqual([]);
        });

        it('cart items are empty array if transport cookie value is not an array', function () {
            setTransportCookie({"cartItems": 'foo'});
            expect(MagentoData.getCartItems()).toEqual([]);
        });

        it('cart items array from transport cookie is returned', function () {
            var testCartItems = ['foo'];
            setTransportCookie({"cartItems": testCartItems});
            expect(MagentoData.getCartItems()).toEqual(testCartItems);
        });

        it('cart total equals to 0,00 € if transport cookie value is not set', function () {
            expect(MagentoData.getCartTotal()).toBe(encodeURIComponent('0,00 €'));
        });

        it('cart total equals to value from transport cookie', function () {
            var testCartTotal = 'foo';
            setTransportCookie({"cartTotal" : testCartTotal});
            expect(MagentoData.getCartTotal()).toBe(testCartTotal);
        });

        it('customer is considered to be not logged in if transport cookie value is not set', function () {
            expect(MagentoData.isCustomerLoggedIn()).toBe(false);
        });

        it('customer is considered to be not logged in if transport cookie value is anything but "1"', function () {
            var testCustomerLogInStatus = 'foo';
            setTransportCookie({"isCustomerLoggedIn" : testCustomerLogInStatus});
            expect(MagentoData.isCustomerLoggedIn()).toBe(false);
        });

        it('customer is considered to be not logged in if transport cookie value is "1"', function () {
            var testCustomerLogInStatus = 1;
            setTransportCookie({"isCustomerLoggedIn" : testCustomerLogInStatus});
            expect(MagentoData.isCustomerLoggedIn()).toBe(true);
        });
    });
});
