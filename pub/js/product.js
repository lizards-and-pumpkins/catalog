define(function () {

    function isDate(dateString) {
        return dateString.match(/^\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}:\d{2}$/);
    }

    return function (productSourceData) {
        this.product = productSourceData;

        this.getName = function () {
            return this.product['attributes']['name'];
        };

        this.getUrlKey = function () {
            return this.product['attributes']['url_key'];
        };

        this.getBrand = function () {
            return this.product['attributes']['brand'];
        };

        this.getGender = function () {
            return this.product['attributes']['gender'];
        };

        this.getMainImage = function () {
            return this.product['images']['medium'][0];
        };

        this.hasSpecialPrice = function () {
            if (!this.product['attributes'].hasOwnProperty('raw_special_price')) {
                return false;
            }

            return this.product['attributes']['raw_price'] > this.product['attributes']['raw_special_price'];
        };

        this.getPrice = function () {
            return this.product['attributes']['price'];
        };

        this.getSpecialPrice = function () {
            return this.product['attributes']['special_price'];
        };

        this.getFinalPriceAsFloat = function() {
            if (this.hasSpecialPrice()) {
                return this.product['attributes']['raw_special_price'] / this.product['attributes']['price_base_unit'];
            }

            return  this.product['attributes']['raw_price'] / this.product['attributes']['price_base_unit'];
        };

        this.hasBasePrice = function () {
            return this.product['attributes'].hasOwnProperty('base_price_amount');
        };

        this.getBasePrice = function () {
            var basePrice = this.getFinalPriceAsFloat() * this.getBasePriceBaseAmount() / this.getBasePriceAmount();
            return Math.round(basePrice * 100) / 100;
        };

        this.getBasePriceBaseAmount = function () {
            return parseFloat(this.product['attributes']['base_price_base_amount']);
        };

        this.getBasePriceAmount = function () {
            return parseFloat(this.product['attributes']['base_price_amount']);
        };

        this.getBasePriceUnit = function () {
            return this.product['attributes']['base_price_unit'];
        };

        this.isNew = function () {
            if ((!this.product['attributes'].hasOwnProperty('news_from_date') ||
                 !isDate(this.product['attributes']['news_from_date'])) &&
                (!this.product['attributes'].hasOwnProperty('news_to_date') ||
                 !isDate(this.product['attributes']['news_to_date']))
            ) {
                return false;
            }

            var currentDate = new Date();

            if (this.product['attributes'].hasOwnProperty('news_from_date')) {
                var newsFromDate = new Date(this.product['attributes']['news_from_date'].replace(/\s/, 'T'));

                if (newsFromDate > currentDate) {
                    return false;
                }
            }

            if (this.product['attributes'].hasOwnProperty('news_to_date')) {
                var newsToDate = new Date(this.product['attributes']['news_to_date'].replace(/\s/, 'T'));

                if (newsToDate < currentDate) {
                    return false;
                }
            }

            return true;
        };
    }
});
