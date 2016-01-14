define(function () {

    var wrapIntoProductLink = function (element, url) {
        var link = document.createElement('A');
        link.href = url;
        link.appendChild(element);
        return link;
    };

    var createProductImage = function (imageUrl, alt) {
        var image = new Image();
        image.src = imageUrl;
        image.alt = alt;
        return image;
    };

    var getBrandLogoSrc = function (brandName) {
        var brand = brandName.toString().toLocaleLowerCase().replace(/\W/, '_');
        return baseUrl + 'media/brands/brands-slider/' + brand + '.png';
    };

    var turnIntoStringIfIsArray = function (operand) {
        if (Array.isArray(operand)) {
            return operand.join(', ');
        }

        return operand;
    };

    function isDate(dateString) {
        return dateString.match(/^\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}:\d{2}$/);
    }

    function isApplicableForNewBadge(productAttributes) {
        if ((!productAttributes.hasOwnProperty('news_from_date') || !isDate(productAttributes['news_from_date'])) &&
            (!productAttributes.hasOwnProperty('news_to_date') || !isDate(productAttributes['news_to_date']))
        ) {
            return false;
        }

        var currentDate = new Date();

        if (productAttributes.hasOwnProperty('news_from_date')) {
            var newsFromDate = new Date(productAttributes['news_from_date'].replace(/\s/, 'T'));

            if (newsFromDate > currentDate) {
                return false;
            }
        }

        if (productAttributes.hasOwnProperty('news_to_date')) {
            var newsToDate = new Date(productAttributes['news_to_date'].replace(/\s/, 'T'));

            if (newsToDate < currentDate) {
                return false;
            }
        }

        return true;
    }

    function createNewBadge() {
        var newBadge = document.createElement('SPAN');
        newBadge.className = 'new-product';
        newBadge.textContent = 'NEW';

        return newBadge;
    }

    function productHasSpecialPrice(productAttributes) {
        if (!productAttributes.hasOwnProperty('raw_special_price')) {
            return false;
        }

        return productAttributes['raw_price'] > productAttributes['raw_special_price'];
    }

    function getFinalPriceAsFloat(productAttributes) {
        if (productHasSpecialPrice(productAttributes)) {
            return productAttributes['raw_special_price'] / productAttributes['price_base_unit'];
        }

        return  productAttributes['raw_price'] / productAttributes['price_base_unit'];
    }

    function createBasePriceIfRequired(productAttributes) {
        var price = getFinalPriceAsFloat(productAttributes),
            basePriceBaseAmount = parseFloat(productAttributes['base_price_base_amount']),
            basePriceAmount = parseFloat(productAttributes['base_price_amount']),
            basePriceUnit = productAttributes['base_price_unit'],
            basePrice = Math.round(price * basePriceBaseAmount / basePriceAmount * 100) / 100,
            basePriceNode = document.createElement('DIV');

        basePriceNode.className = 'base-price';
        basePriceNode.innerHTML = basePricePattern.replace(/%s/, basePriceBaseAmount)
            .replace(/%s/, basePriceUnit)
            .replace(/%s/, basePrice);

        return basePriceNode;
    }

    function createPricesBlock(productAttributes) {
        var container = document.createElement('DIV'),
            price = document.createElement('DIV'),
            hasSpecialPrice = productHasSpecialPrice(productAttributes);

        price.textContent = productAttributes['price'];
        price.className = hasSpecialPrice ? 'old-price' : 'regular-price';

        container.className = 'price-container';
        container.appendChild(price);

        if (hasSpecialPrice) {
            var specialPrice = document.createElement('DIV');
            specialPrice.textContent = productAttributes['special_price'];
            specialPrice.className = 'special-price';
            container.appendChild(specialPrice);
        }

        if (productAttributes.hasOwnProperty('base_price_amount') && typeof basePricePattern !== 'undefined') {
            container.appendChild(createBasePriceIfRequired(productAttributes));
        }

        return container;
    }

    return {
        renderGrid: function (productGridJson, productGridPlaceholderSelector) {
            var productGridPlaceholder = document.querySelector(productGridPlaceholderSelector);

            if (null === productGridPlaceholder) {
                return;
            }

            var grid = document.createElement('UL');
            grid.className = 'products-grid';

            productGridJson.map(function (product) {
                var mainImage = product['images']['medium'][0],
                    productLi = document.createElement('LI'),
                    container = document.createElement('DIV'),
                    title = document.createElement('H2'),
                    gender = document.createElement('P'),
                    productUrl = baseUrl + product['attributes']['url_key'],
                    productImage = createProductImage(mainImage['url'], mainImage['label']);

                title.textContent = product['attributes']['name'];
                gender.textContent = turnIntoStringIfIsArray(product['attributes']['gender']);

                container.style.backgroundImage = 'url("' + getBrandLogoSrc(product['attributes']['brand']) + '")';
                container.className = 'grid-cell-container';

                if (isApplicableForNewBadge(product['attributes'])) {
                    container.appendChild(createNewBadge());
                }

                container.appendChild(wrapIntoProductLink(productImage, productUrl));
                container.appendChild(wrapIntoProductLink(title, productUrl));
                container.appendChild(gender);
                container.appendChild(createPricesBlock(product['attributes']));

                productLi.appendChild(container);
                grid.appendChild(productLi);
            });

            productGridPlaceholder.appendChild(grid);
        }
    }
});
