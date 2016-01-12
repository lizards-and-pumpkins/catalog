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
