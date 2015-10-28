define(function () {
    var lazyLoadedBaseUrl = null;

    var getBaseUrl = function () {
        if (null === lazyLoadedBaseUrl) {
            lazyLoadedBaseUrl = location.href.replace(/[^\/]*$/, '');
        }
        return lazyLoadedBaseUrl;
    };

    var wrapIntoProductLink = function (element, url) {
        var link = document.createElement('A');
        link.href = url;
        link.appendChild(element);
        return link;
    };

    var createProductImage = function (fileName, alt) {
        var image = new Image();
        image.src = 'media/product/medium/' + fileName;
        image.alt = alt;
        return image;
    };

    var getBrandLogoSrc = function (brandName) {
        var brand = brandName.toString().toLocaleLowerCase().replace(/\W/, '_');
        return 'images/brands/brands-slider/' + brand + '.png';
    };

    var turnIntoStringIfIsArray = function (operand) {
        if (operand.isArray) {
            return operand.join(', ');
        }

        return operand;
    };

    return {
        renderGrid: function (productGridJson, productPrices, productGridPlaceholderSelector) {
            var productGridPlaceholder = document.querySelector(productGridPlaceholderSelector);

            if (null === productGridPlaceholder) {
                return;
            }

            var grid = document.createElement('UL');
            grid.className = 'products-grid';

            productGridJson.map(function (product, index) {
                var productLi = document.createElement('LI'),
                    container = document.createElement('DIV'),
                    title = document.createElement('H2'),
                    gender = document.createElement('P'),
                    productUrl = getBaseUrl() + product['attributes']['url_key'],
                    productImage = createProductImage(product['images'][0]['file'], product['images'][0]['label']),
                    priceNode = document.createTextNode(productPrices[index]);

                title.textContent = product['attributes']['name'];
                gender.textContent = turnIntoStringIfIsArray(product['attributes']['gender']);

                container.style.backgroundImage = 'url("' + getBrandLogoSrc(product['attributes']['brand']) + '")';
                container.className = 'grid-cell-container';

                container.appendChild(wrapIntoProductLink(productImage, productUrl));
                container.appendChild(wrapIntoProductLink(title, productUrl));
                container.appendChild(gender);
                container.appendChild(priceNode);
                productLi.appendChild(container);
                grid.appendChild(productLi);
            });

            productGridPlaceholder.appendChild(grid);
        }
    }
});
