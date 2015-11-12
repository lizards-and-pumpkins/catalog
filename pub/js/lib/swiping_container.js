define(function () {

    var createArrow = function (className, outerContainer, direction) {
        var arrow = outerContainer.parentNode.querySelector('.' + className);

        if (null === arrow) {
            arrow = document.createElement('A');
            arrow.className = className;
            arrow.addEventListener('click', function () {
                swipeHorizontal(outerContainer, direction)
            }, true);

            outerContainer.appendChild(arrow);
        }

        return arrow;
    };

    var swipeHorizontal = function (container, direction) {
        container.scrollLeft += 150 * (direction == 'left' ? -1 : 1);
    };

    return function toggleSwipingArrows(outerContainerSelector, innerContainerSelector) {
        var swipeContainers = document.querySelectorAll(outerContainerSelector);
        Array.prototype.map.call(swipeContainers, function (outerContainer) {
            var prevArr = createArrow('swipe-prev', outerContainer, 'left'),
                nextArr = createArrow('swipe-next', outerContainer, 'right'),
                scrollPosition = outerContainer.scrollLeft,
                innerContainer = outerContainer.querySelector(innerContainerSelector);

            if (null === innerContainer) {
                return;
            }

            outerContainer.addEventListener('scroll', function () {
                toggleSwipingArrows(outerContainerSelector, 'ul');
            }, true);

            prevArr.style.opacity = scrollPosition > 0 ? 1 : 0;
            nextArr.style.opacity = innerContainer.offsetWidth - document.body.clientWidth > scrollPosition ? 1 : 0;
        });
    }
});
