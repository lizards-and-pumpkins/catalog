define(['product', 'lib/translate'], function (Product, translate) {

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
        return baseUrl + 'media/brands/product-grid/' + brand + '.png';
    };

    var turnIntoStringIfIsArray = function (operand) {
        if (Array.isArray(operand)) {
            return operand.join(', ');
        }

        return operand;
    };

    function createNewBadge() {
        var newBadge = document.createElement('SPAN');
        newBadge.className = 'new-product';
        newBadge.textContent = 'NEW';

        return newBadge;
    }

    function createBasePrice(product) {
        var basePriceNode = document.createElement('DIV');

        basePriceNode.className = 'base-price';
        basePriceNode.innerHTML = basePricePattern.replace(/%s/, product.getBasePriceBaseAmount())
            .replace(/%s/, product.getBasePriceUnit())
            .replace(/%s/, product.getBasePrice());

        return basePriceNode;
    }

    function createPricesBlock(product) {
        var container = document.createElement('DIV'),
            price = document.createElement('DIV');

        price.textContent = product.getPrice();
        price.className = product.hasSpecialPrice() ? 'old-price' : 'regular-price';

        container.className = 'price-container';
        container.appendChild(price);

        if (product.hasSpecialPrice()) {
            var specialPrice = document.createElement('DIV');
            specialPrice.textContent = product.getSpecialPrice();
            specialPrice.className = 'special-price';
            container.appendChild(specialPrice);
        }

        if (product.hasBasePrice() && typeof basePricePattern !== 'undefined') {
            container.appendChild(createBasePrice(product));
        }

        return container;
    }

    var createYouSaveBlock = function (product) {
        var container = document.createElement('P');
        container.textContent = translate('Save %s% now', product.getDiscountPercentage());
        container.className = 'you-save';
        return container;
    };

    function createGridItem(productSourceData) {
        var product = new Product(productSourceData),
            mainImage = product.getMainImage(),
            productLi = document.createElement('LI'),
            container = document.createElement('DIV'),
            title = document.createElement('H2'),
            gender = document.createElement('P'),
            productUrl = baseUrl + product.getUrlKey(),
            productImage = createProductImage(mainImage['url'], mainImage['label']);

        title.textContent = product.getName();
        gender.textContent = turnIntoStringIfIsArray(product.getGender());

        container.style.backgroundImage = 'url("' + getBrandLogoSrc(product.getBrand()) + '")';
        container.className = 'grid-cell-container';

        if (product.isNew()) {
            container.appendChild(createNewBadge());
        }

        container.appendChild(wrapIntoProductLink(productImage, productUrl));
        container.appendChild(wrapIntoProductLink(title, productUrl));
        container.appendChild(gender);
        container.appendChild(createPricesBlock(product));

        if (product.getDiscountPercentage() >= 5) {
            container.appendChild(createYouSaveBlock(product));
        }

        productLi.appendChild(container);

        return productLi;
    }

    return {
        renderGrid: function (productGridJson, productGridPlaceholderSelector) {
            var productGridPlaceholder = document.querySelector(productGridPlaceholderSelector);

            if (null === productGridPlaceholder) {
                return;
            }

            var grid = document.createElement('UL');
            grid.className = 'products-grid';

            productGridPlaceholder.appendChild(grid);

            productGridJson.map(function (productSourceData) {
                grid.appendChild(createGridItem(productSourceData));
            });
        }
    }
});
