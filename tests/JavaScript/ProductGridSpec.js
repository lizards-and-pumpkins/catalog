define(['../../pub/js/product_grid'], function (ProductGrid) {
    var gridContainerId = 'grid-container-id',
        gridContainerSelector = '#' + gridContainerId;

    var testProductName = 'foo',
        testProductUrlKey = 'foo.html',
        testProductBrand = 'bar',
        testProductGenders = ['male'],
        testProductImageUrl = 'http://example.com/foo.png',
        testProductImageLabel = 'foo',
        testProductPrice = '$18.00';

    function createTemporaryElement() {
        var gridContainer = document.createElement('DIV');
        gridContainer.id = gridContainerId;
        document.getElementsByTagName('BODY')[0].appendChild(gridContainer);
    }

    function removeTemporaryElement() {
        document.getElementsByTagName('BODY')[0].removeChild(document.getElementById(gridContainerId));
    }

    function getTestProductData() {
        return {
            "attributes": {
                "name": testProductName,
                "url_key": testProductUrlKey,
                "gender": testProductGenders,
                "brand": [testProductBrand],
                "price": testProductPrice,
                "raw_price": '1800',
                "price_base_unit": '100'
            },
            "images": {
                "medium": [
                    {
                        "url": testProductImageUrl,
                        "label": testProductImageLabel
                    }
                ]
            }
        };
    }

    function getTestProductDataWithSpecialPrice(specialPrice, rawSpecialPrice) {
        var productData = getTestProductData();
        productData['attributes']['special_price'] = specialPrice;
        productData['attributes']['raw_special_price'] = rawSpecialPrice;

        return productData;
    }

    function getTestProductDataWithBasePrice(basePriceAmount, basePriceUnit, basePriceBaseAmount) {
        var productData = getTestProductData();
        productData['attributes']['base_price_amount'] = basePriceAmount;
        productData['attributes']['base_price_unit'] = basePriceUnit;
        productData['attributes']['base_price_base_amount'] = basePriceBaseAmount;

        return productData;
    }

    function getTestProductDataWithProductNewInformation() {
        var productData = getTestProductData();
        productData['attributes']['news_from_date'] = '2000-01-01 00:00:00';
        productData['attributes']['news_to_date'] = '3000-01-01 00:00:00';

        return productData;
    }

    describe('Product grid', function () {
        beforeEach(function () {
            createTemporaryElement();
            window.baseUrl = 'http://example.com/';
        });

        afterEach(function () {
            removeTemporaryElement();
            delete window.baseUrl;
        });

        it('is not rendered if non existing container selector is specified', function () {
            var documentBodyHtmlBefore = document.body.innerHTML;
            ProductGrid.renderGrid([], 'non-existing-selector');
            expect(document.body.innerHTML).toBe(documentBodyHtmlBefore);
        });

        it('is an empty unordered list if there are no products', function () {
            ProductGrid.renderGrid([], gridContainerSelector);
            var gridContainer = document.querySelector(gridContainerSelector);
            expect(gridContainer.innerHTML).toMatch(/^<ul[^>]*><\/ul>$/);
        });

        it('is an unordered list with "products-grid" class', function () {
            ProductGrid.renderGrid([], gridContainerSelector);
            expect(document.querySelector(gridContainerSelector + ' > ul').className).toMatch(/\bproducts-grid\b/);
        });

        it('contains a product', function () {
            ProductGrid.renderGrid([getTestProductData()], gridContainerSelector);
            var gridItems = document.querySelectorAll(gridContainerSelector + ' > ul > li');
            expect(gridItems.length).toBe(1);
        });

        it('has each product wrapped into DIV element with "grid-cell-container" class', function () {
            ProductGrid.renderGrid([getTestProductData()], gridContainerSelector);
            var gridItemContainers = document.querySelectorAll(gridContainerSelector + ' > ul > li > div');

            expect(gridItemContainers.length).toBe(1);
            Array.prototype.map.call(gridItemContainers, function (gridItemContainer) {
                expect(gridItemContainer.className).toMatch(/\bgrid-cell-container\b/);
            });
        });

        it('product container has a brand logo as a background image', function () {
            ProductGrid.renderGrid([getTestProductData()], gridContainerSelector);
            var gridItemContainers = document.querySelectorAll(gridContainerSelector + ' > ul > li > div');

            expect(gridItemContainers.length).toBe(1);
            Array.prototype.map.call(gridItemContainers, function (gridItemContainer) {
                var expectedImage = 'url(http://example.com/media/brands/brands-slider/' + testProductBrand + '.png)';
                expect(gridItemContainer.style.backgroundImage).toBe(expectedImage);
            });
        });

        describe('product', function () {
            it('has a "new" badge if it is new', function () {
                var testProductData = getTestProductData(),
                    testNewProductData = getTestProductDataWithProductNewInformation();

                ProductGrid.renderGrid([testNewProductData, testProductData], gridContainerSelector);
                var gridItemContainers = document.querySelectorAll(gridContainerSelector + ' > ul > li > div'),
                    gridItemContainersArray = Array.prototype.slice.call(gridItemContainers),
                    newBadgeHtml = '<span class="new-product">NEW</span>';

                expect(gridItemContainersArray[0].innerHTML).toContain(newBadgeHtml);
                expect(gridItemContainersArray[1].innerHTML).not.toContain(newBadgeHtml);
            });

            it('has an image wrapped product link', function () {
                ProductGrid.renderGrid([getTestProductData()], gridContainerSelector);
                var gridItems = document.querySelectorAll(gridContainerSelector + ' > ul > li');

                Array.prototype.map.call(gridItems, function (gridItem) {
                    var imageTag = '<img src="' + testProductImageUrl + '" alt="' + testProductImageLabel + '">',
                        expectedHtml = '<a href="' + baseUrl + testProductUrlKey + '">' + imageTag + '</a>';
                    expect(gridItem.innerHTML).toContain(expectedHtml);
                });
            });

            it('has a title wrapped into H2 tag and product link', function () {
                ProductGrid.renderGrid([getTestProductData()], gridContainerSelector);
                var gridItems = document.querySelectorAll(gridContainerSelector + ' > ul > li');

                Array.prototype.map.call(gridItems, function (gridItem) {
                    var titleHTML = '<h2>' + testProductName + '</h2>',
                        expectedHtml = '<a href="' + baseUrl + testProductUrlKey + '">' + titleHTML + '</a>';
                    expect(gridItem.innerHTML).toContain(expectedHtml);
                });
            });

            it('has a gender wrapped into P tag', function () {
                ProductGrid.renderGrid([getTestProductData()], gridContainerSelector);
                var gridItems = document.querySelectorAll(gridContainerSelector + ' > ul > li');

                Array.prototype.map.call(gridItems, function (gridItem) {
                    var expectedGenderHtml = '<p>' + testProductGenders[0] + '</p>';
                    expect(gridItem.innerHTML).toContain(expectedGenderHtml);
                });
            });

            it('has multiple genders comma concatenated', function () {
                testProductGenders = ['male', 'female'];

                ProductGrid.renderGrid([getTestProductData()], gridContainerSelector);
                var gridItems = document.querySelectorAll(gridContainerSelector + ' > ul > li');

                Array.prototype.map.call(gridItems, function (gridItem) {
                    var expectedGenderHtml = '<p>' + testProductGenders[0] + ', ' + testProductGenders[1] + '</p>';
                    expect(gridItem.innerHTML).toContain(expectedGenderHtml);
                });
            });

            it('has a price', function () {
                ProductGrid.renderGrid([getTestProductData()], gridContainerSelector);
                var gridItems = document.querySelectorAll(gridContainerSelector + ' > ul > li');

                Array.prototype.map.call(gridItems, function (gridItem) {
                    var priceHtml = '<div class="regular-price">' + testProductPrice + '</div>',
                        expectedHtml = '<div class="price-container">' + priceHtml + '</div>';
                    expect(gridItem.innerHTML).toContain(expectedHtml);
                });
            });

            it('has a price and a special price', function () {
                var specialPrice = '$17.00',
                    rawSpecialPrice = '1700',
                    productData = getTestProductDataWithSpecialPrice(specialPrice, rawSpecialPrice);

                ProductGrid.renderGrid([productData], gridContainerSelector);
                var gridItems = document.querySelectorAll(gridContainerSelector + ' > ul > li');

                Array.prototype.map.call(gridItems, function (gridItem) {
                    var priceHtml = '<div class="old-price">' + testProductPrice + '</div>',
                        specialPriceHtml = '<div class="special-price">' + specialPrice + '</div>',
                        expectedHtml = '<div class="price-container">' + priceHtml + specialPriceHtml + '</div>';
                    expect(gridItem.innerHTML).toContain(expectedHtml);
                });
            });

            it('has a base price', function () {
                var basePriceUnit = 'G',
                    basePriceAmount = '50',
                    basePriceBaseAmount = '100',
                    productData = getTestProductDataWithBasePrice(basePriceAmount, basePriceUnit, basePriceBaseAmount);

                window.basePricePattern = '%s %s %s';

                ProductGrid.renderGrid([productData], gridContainerSelector);
                var gridItems = document.querySelectorAll(gridContainerSelector + ' > ul > li');

                Array.prototype.map.call(gridItems, function (gridItem) {
                    var expectedHtml = '<div class="base-price">100 G 36</div>';
                    expect(gridItem.innerHTML).toContain(expectedHtml);
                });

                delete window.basePricePattern;
            });

            it('has no saving information if there is no special price defined', function () {
                ProductGrid.renderGrid([getTestProductData()], gridContainerSelector);
                var gridItems = document.querySelectorAll(gridContainerSelector + ' > ul > li'),
                    unexpectedHtml = '<p class="you-save">';

                Array.prototype.map.call(gridItems, function (gridItem) {
                    expect(gridItem.innerHTML).not.toContain(unexpectedHtml);
                });
            });

            it('has no saving information if saving is less than 5%', function () {
                ProductGrid.renderGrid([getTestProductDataWithSpecialPrice('%17.99', '1799')], gridContainerSelector);
                var gridItems = document.querySelectorAll(gridContainerSelector + ' > ul > li'),
                    unexpectedHtml = '<p class="you-save">';

                Array.prototype.map.call(gridItems, function (gridItem) {
                    expect(gridItem.innerHTML).not.toContain(unexpectedHtml);
                });
            });


            it('has a saving information if saving is greater or equals to 5%', function () {
                ProductGrid.renderGrid([getTestProductDataWithSpecialPrice('%17.00', '1700')], gridContainerSelector);
                var gridItems = document.querySelectorAll(gridContainerSelector + ' > ul > li'),
                    expectedHtml = '<p class="you-save">Save 6% now';

                Array.prototype.map.call(gridItems, function (gridItem) {
                    expect(gridItem.innerHTML).toContain(expectedHtml);
                });
            });
        });
    });
});
