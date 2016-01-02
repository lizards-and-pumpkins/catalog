define(function () {

    function createArrow(className, outerContainer, direction) {
        var arrow = document.createElement('A');
        arrow.className = className;
        arrow.addEventListener('click', function () {
            swipeHorizontal(outerContainer, direction)
        }, true);

        outerContainer.appendChild(arrow);

        return arrow;
    }

    function getArrow(className, outerContainer, direction) {
        var arrow = outerContainer.parentNode.querySelector('.' + className);

        if (null !== arrow) {
            return arrow;
        }

        return createArrow(className, outerContainer, direction);
    }

    function swipeHorizontal(container, direction) {
        container.scrollLeft += 150 * (direction == 'left' ? -1 : 1);
    }

    function recalculateArrowsVisibility(outerContainer, innerContainer) {
        var prevArr = getArrow('swipe-prev', outerContainer, 'left'),
            nextArr = getArrow('swipe-next', outerContainer, 'right'),
            scrollPosition = outerContainer.scrollLeft;

        prevArr.style.opacity = scrollPosition > 0 ? 1 : 0;
        nextArr.style.opacity = outerContainer.offsetWidth + scrollPosition < innerContainer.clientWidth ? 1 : 0;
    }

    return function (outerContainerSelector, innerContainerSelector) {
        Array.prototype.map.call(document.querySelectorAll(outerContainerSelector), function (outerContainer) {
            var innerContainer = outerContainer.querySelector(innerContainerSelector);

            if (null === innerContainer) {
                return;
            }

            outerContainer.addEventListener('scroll', function () {
                recalculateArrowsVisibility(outerContainer, innerContainer);
            }, true);

            recalculateArrowsVisibility(outerContainer, innerContainer);
        });
    }
});
