define(['../../pub/js/product'], function (Product) {

    function nonPositiveNumberProvider() {
        return [-1, 0, '', ' ', null];
    }

    describe('Product', function () {
        var product;

        it('name is returned', function () {
            product = new Product({"attributes": {"name": 'foo'}});
            expect(product.getName()).toBe('foo');
        });

        it('URL key is returned', function () {
            product = new Product({"attributes": {"url_key": 'foo'}});
            expect(product.getUrlKey()).toBe('foo');
        });

        it('brand is returned', function () {
            product = new Product({"attributes": {"brand": 'foo'}});
            expect(product.getBrand()).toBe('foo');
        });

        it('gender is returned', function () {
            product = new Product({"attributes": {"gender": 'foo'}});
            expect(product.getGender()).toBe('foo');
        });

        it('first image is returned', function () {
            product = new Product({"images": {"medium": [{"url": 'foo'}]}});
            expect(product.getMainImage()).toEqual({"url": 'foo'});
        });

        it('has no special price if no raw special price is specified', function () {
            product = new Product({"attributes": {"raw_price": '500'}});
            expect(product.hasSpecialPrice()).toBe(false);
        });

        it('has no special price if raw special price is greater than raw regular price', function () {
            product = new Product({"attributes": {"raw_price": '500', "raw_special_price": '1000'}});
            expect(product.hasSpecialPrice()).toBe(false);
        });

        it('has no special price if raw special price is equal to raw regular price', function () {
            product = new Product({"attributes": {"raw_price": '500', "raw_special_price": '500'}});
            expect(product.hasSpecialPrice()).toBe(false);
        });

        it('has a special price if raw special price is lower than raw regular price', function () {
            product = new Product({"attributes": {"raw_price": '1000', "raw_special_price": '500'}});
            expect(product.hasSpecialPrice()).toBe(true);
        });

        it('price is returned', function () {
            product = new Product({"attributes": {"price": '107,94 €'}});
            expect(product.getPrice()).toBe('107,94 €');
        });

        it('special price is returned', function () {
            product = new Product({"attributes": {"special_price": '107,94 €'}});
            expect(product.getSpecialPrice()).toBe('107,94 €');
        });

        it('has no base price if no base price amount is specified', function () {
            product = new Product({"attributes": {}});
            expect(product.hasBasePrice()).toBe(false);
        });

        it('has no base price if base price amount is not a positive number', function () {
            nonPositiveNumberProvider().map(function (nonPositiveNUmber) {
                product = new Product({"attributes": {"base_price_amount": nonPositiveNUmber}});
                expect(product.hasBasePrice()).toBe(false);
            });
        });

        it('has base price if base price amount is a positive number', function () {
            product = new Product({"attributes": {"base_price_amount": '100'}});
            expect(product.hasBasePrice()).toBe(true);
        });

        it('base price is returned', function () {
            product = new Product({
                "attributes": {
                    "base_price_amount": '35',
                    "base_price_base_amount": '10',
                    "raw_price": '200',
                    "price_base_unit": '100'
                }
            });
            expect(product.getBasePrice()).toBe(.57);
        });

        it('base price calculated from special price is returned', function () {
            product = new Product({
                "attributes": {
                    "base_price_amount": '35',
                    "base_price_base_amount": '10',
                    "raw_price": '200',
                    "raw_special_price": '189',
                    "price_base_unit": '100'
                }
            });
            expect(product.getBasePrice()).toBe(.54);
        });

        it('base price base amount is returned', function () {
            product = new Product({"attributes": {"base_price_base_amount": '100'}});
            expect(product.getBasePriceBaseAmount()).toBe(100);
        });

        it('base price amount is returned', function () {
            product = new Product({"attributes": {"base_price_amount": '100'}});
            expect(product.getBasePriceAmount()).toBe(100);
        });

        it('base price unit is returned', function () {
            product = new Product({"attributes": {"base_price_unit": 'G'}});
            expect(product.getBasePriceUnit()).toBe('G');
        });
        
        it('is not new if neither "new from" nor "new to" date is specified', function () {
            product = new Product({"attributes": {}});
            expect(product.isNew()).toBe(false);
        });
        
        it('is not new if "new from" date is in a future', function () {
            product = new Product({"attributes": {"news_from_date": '3000-01-01 00:00:00'}});
            expect(product.isNew()).toBe(false);
        });

        it('is not new if "new to" date is in a past', function () {
            product = new Product({"attributes": {"news_to_date": '2000-01-01 00:00:00'}});
            expect(product.isNew()).toBe(false);
        });

        it('is new if "new from" date is in a past and "new to" date is not set', function () {
            product = new Product({"attributes": {"news_from_date": '2000-01-01 00:00:00'}});
            expect(product.isNew()).toBe(true);
        });

        it('is new if "new to" date is in a future and "new from" date is not set', function () {
            product = new Product({"attributes": {"news_to_date": '3000-01-01 00:00:00'}});
            expect(product.isNew()).toBe(true);
        });

        it('is new if "new from" date is in a past and "new to" date is in a future', function () {
            product = new Product({
                "attributes": {
                    "news_from_date": '2000-01-01 00:00:00',
                    "news_to_date": '3000-01-01 00:00:00'
                }
            });
            expect(product.isNew()).toBe(true);
        });

        it('discount percentage is returned', function () {
            product = new Product({"attributes": {"raw_price": '1800', "raw_special_price": '1700'}});
            expect(product.getDiscountPercentage()).toBe(100 - Math.round(1700 * 100 / 1800));
        });
    });
});
