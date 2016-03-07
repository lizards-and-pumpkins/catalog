define(function () {
    return function (productSourceData) {
        var product = productSourceData;

        function isDate(dateString) {
            return dateString.match(/^\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}:\d{2}$/);
        }

        function getRawPrice() {
            return parseInt(product['attributes']['raw_price']);
        }

        function getRawSpecialPrice() {
            return parseInt(product['attributes']['raw_special_price']);
        }

        function getFinalRawPrice(product) {
            if (product.hasSpecialPrice()) {
                return getRawSpecialPrice();
            }

            return getRawPrice();
        }

        this.getSku = function () {
            return product['product_id'];
        };

        this.getName = function () {
            return product['attributes']['name'];
        };

        this.getUrlKey = function () {
            return product['attributes']['url_key'];
        };

        this.getBrand = function () {
            return product['attributes']['brand'];
        };

        this.getGender = function () {
            return product['attributes']['gender'];
        };

        this.getMainImage = function () {
            return product['images']['medium'][0];
        };

        this.hasSpecialPrice = function () {
            if (!product['attributes'].hasOwnProperty('raw_special_price')) {
                return false;
            }

            return getRawPrice(this) > getRawSpecialPrice(this);
        };

        this.getPrice = function () {
            return product['attributes']['price'];
        };

        this.getSpecialPrice = function () {
            return product['attributes']['special_price'];
        };

        this.hasBasePrice = function () {
            return product['attributes'].hasOwnProperty('base_price_amount') && this.getBasePriceAmount() > 0;
        };

        this.getBasePrice = function () {
            var price = getFinalRawPrice(this) / product['attributes']['price_base_unit'],
                basePrice = price * this.getBasePriceBaseAmount() / this.getBasePriceAmount();

            return Math.round(basePrice * 100) / 100;
        };

        this.getBasePriceBaseAmount = function () {
            return parseFloat(product['attributes']['base_price_base_amount']);
        };

        this.getBasePriceAmount = function () {
            return parseFloat(product['attributes']['base_price_amount']);
        };

        this.getBasePriceUnit = function () {
            return product['attributes']['base_price_unit'];
        };

        this.isNew = function () {
            if ((!product['attributes'].hasOwnProperty('news_from_date') ||
                 !isDate(product['attributes']['news_from_date'])) &&
                (!product['attributes'].hasOwnProperty('news_to_date') ||
                 !isDate(product['attributes']['news_to_date']))
            ) {
                return false;
            }

            var currentDate = new Date();

            if (product['attributes'].hasOwnProperty('news_from_date')) {
                var newsFromDate = new Date(product['attributes']['news_from_date'].replace(/\s/, 'T'));

                if (newsFromDate > currentDate) {
                    return false;
                }
            }

            if (product['attributes'].hasOwnProperty('news_to_date')) {
                var newsToDate = new Date(product['attributes']['news_to_date'].replace(/\s/, 'T'));

                if (newsToDate < currentDate) {
                    return false;
                }
            }

            return true;
        };

        this.getDiscountPercentage = function () {
            return 100 - Math.round(getRawSpecialPrice(this) * 100 / getRawPrice(this));
        };

        this.getImageUrlByNumber = function (size, number) {
            if (typeof product['images'][size] === 'undefined' ||
                typeof product['images'][size][number - 1] === 'undefined'
            ) {
                return null;
            }

            return product['images'][size][number - 1]['url'];
        };

        this.getNumberOfImages = function () {
            return product['images']['original'].length;
        };

        this.getFinalPrice = function () {
            if (this.hasSpecialPrice() && getRawSpecialPrice() < getRawPrice()) {
                return this.getSpecialPrice();
            }

            return this.getPrice();
        };
    }
});
