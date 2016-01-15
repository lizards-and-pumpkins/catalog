define(function () {

    function movementStart(element) {
        var timeoutId = setTimeout(function () {
            element.scrollLeft += 5;
            if (element.scrollWidth - element.offsetWidth === element.scrollLeft) {
                clearTimeout(timeoutId);
                return;
            }
            movementStart(element);
        }, 10);
    }

    function movementStop(element) {
        element.scrollLeft = 0;
    }

    return function (selector) {
        Array.prototype.map.call(document.querySelectorAll(selector), function (element) {
            element.className += ' b-scroller-text';

            var scrollContainer = document.createElement('DIV');
            scrollContainer.className = 'b-scroller-body';
            scrollContainer.appendChild(element.cloneNode(true));

            element.parentNode.replaceChild(scrollContainer, element);

            if (scrollContainer.scrollWidth <= scrollContainer.offsetWidth) {
                return;
            }

            var scrollRight = document.createElement('DIV');
            scrollRight.className = 'b-scroller-right';
            scrollRight.addEventListener('mouseover', function () { movementStart(scrollContainer) });
            scrollRight.addEventListener('mouseout', function () { movementStop(scrollContainer) });

            scrollContainer.parentNode.style.position = 'relative';
            scrollContainer.parentNode.style.display = 'block';
            scrollContainer.parentNode.appendChild(scrollRight);
        });
    }
});
