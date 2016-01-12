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

    function isApplicableForNewBadge(productAttributes) {
        if (!productAttributes.hasOwnProperty('news_from_date') && !productAttributes.hasOwnProperty('news_to_date')) {
            return false;
        }

        var currentDate = new Date();

        if (productAttributes.hasOwnProperty('news_from_date')) {
            var newsFromDate = new Date(productAttributes['news_from_date']);

            if (newsFromDate < currentDate) {
                return false;
            }
        }

        if (productAttributes.hasOwnProperty('news_to_date')) {
            var newsToDate = new Date(productAttributes['news_to_date']);

            if (newsToDate > currentDate) {
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
                    productImage = createProductImage(mainImage['url'], mainImage['label']),
                    price = document.createElement('SPAN'),
                    hasSpecialPrice = product['attributes']['special_price'];

                title.textContent = product['attributes']['name'];
                gender.textContent = turnIntoStringIfIsArray(product['attributes']['gender']);

                price.textContent = product['attributes']['price'];
                price.className = hasSpecialPrice ? 'old-price' : 'regular-price';

                container.style.backgroundImage = 'url("' + getBrandLogoSrc(product['attributes']['brand']) + '")';
                container.className = 'grid-cell-container';

                if (isApplicableForNewBadge(product['attributes'])) {
                    container.appendChild(createNewBadge());
                }

                container.appendChild(wrapIntoProductLink(productImage, productUrl));
                container.appendChild(wrapIntoProductLink(title, productUrl));
                container.appendChild(gender);
                container.appendChild(price);

                if (hasSpecialPrice) {
                    var specialPrice = document.createElement('SPAN');
                    specialPrice.textContent = product['attributes']['special_price'];
                    specialPrice.className = 'special-price';
                    container.appendChild(specialPrice);
                }

                productLi.appendChild(container);
                grid.appendChild(productLi);
            });

            productGridPlaceholder.appendChild(grid);
        }
    }
});
