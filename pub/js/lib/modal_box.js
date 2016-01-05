define(['lib/domReady', 'lib/ajax'], function (domReady, callAjax) {

    domReady(function () {
        window.addEventListener('resize', centerPopup);
        window.addEventListener('orientationchange', centerPopup);
    });

    function centerPopup() {
        var popup = document.getElementById('modal-popup');

        if (null === popup) {
            return;
        }

        var viewportWidth = Math.floor(document.body.clientWidth / 100 * 90),
            viewportHeight = Math.floor(document.body.clientHeight / 100 * 90);

        popup.style.top = '';
        popup.style.left = '';

        popup.style.width = viewportWidth > popup.offsetWidth ? '' : viewportWidth + 'px';
        popup.style.marginLeft = popup.offsetWidth / 2 * -1 + 'px';

        popup.style.height = viewportHeight > popup.offsetHeight ? '' : viewportHeight + 'px';
        popup.style.marginTop = popup.offsetHeight / 2 * -1 + 'px';

        popup.style.top = '50%';
        popup.style.left = '50%';

        var contentContainer = document.getElementById('modal-box-content');
        contentContainer.style.height = viewportHeight > popup.offsetHeight ? '' : popup.offsetHeight + 'px';
    }

    function hidePopupAndOverlayIfShown() {
        var elementsToRemove = document.querySelectorAll('#modal-popup, body > .modal-box-overlay');

        Array.prototype.map.call(elementsToRemove, function (element) {
            element.remove();
        });
    }

    function processContent(content) {
        if (!content.match(/^https?:\/\//i)) {
            return content;
        }

        callAjax(content, function (responseText) {
            document.getElementById('modal-box-content').innerHTML = responseText;
            centerPopup();
        });

        return 'loading .. ';
    }

    function createCloseButton() {
        var closeButton = document.createElement('SPAN');
        closeButton.className = 'popup-close';
        closeButton.addEventListener('click', hidePopupAndOverlayIfShown);

        return closeButton;
    }

    function showOverlay() {
        var overlay = document.createElement('DIV');
        overlay.className = 'modal-box-overlay';
        overlay.style.width = document.body.clientWidth + 'px';
        overlay.style.height = document.body.clientHeight + 'px';

        document.body.appendChild(overlay);
    }

    function showPopup(content) {
        var contentWrapper = document.createElement('DIV');
        contentWrapper.id = 'modal-box-content';
        contentWrapper.innerHTML = processContent(content);

        var popup = document.createElement('DIV');
        popup.id = 'modal-popup';
        popup.appendChild(contentWrapper);
        popup.appendChild(createCloseButton());

        document.body.appendChild(popup);
    }

    return function (content) {
        if (typeof content !== 'string' || content.length === 0) {
            return;
        }

        hidePopupAndOverlayIfShown();
        showOverlay();
        showPopup(content);
        centerPopup();
    }
});
