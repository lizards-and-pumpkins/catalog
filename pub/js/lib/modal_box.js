define(['lib/domReady', 'lib/ajax'], function (domReady, callAjax) {
    var popup,
        overlay,
        closeButton;

    domReady(function () {
        window.addEventListener('resize', centerPopup);
        window.addEventListener('orientationchange', centerPopup);
    });

    function centerPopup() {
        if (typeof popup === 'undefined' || typeof closeButton === 'undefined') {
            return;
        }

        var viewportWidth = Math.floor(document.body.clientWidth / 100 * 85),
            viewportHeight = Math.floor(window.innerHeight / 100 * 85);

        popup.style.top = '';
        popup.style.left = '';

        popup.style.width = viewportWidth > popup.offsetWidth ? '' : viewportWidth + 'px';
        popup.style.marginLeft = popup.offsetWidth / 2 * -1 + 'px';

        popup.style.height = viewportHeight > popup.offsetHeight ? '' : viewportHeight + 'px';
        popup.style.marginTop = popup.offsetHeight / 2 * -1 + 'px';

        popup.style.top = '50%';
        popup.style.left = '50%';

        closeButton.style.top = popup.offsetTop - closeButton.offsetHeight/2 + 'px';
        closeButton.style.left = popup.offsetLeft + popup.offsetWidth - closeButton.offsetWidth/2 + 'px';
    }

    function hidePopupAndOverlayIfShown() {
        if (typeof popup === 'undefined' || typeof overlay === 'undefined' || typeof closeButton === 'undefined') {
            return;
        }

        popup.parentNode.removeChild(popup);
        overlay.parentNode.removeChild(overlay);
        closeButton.parentNode.removeChild(closeButton);
    }

    function processContent(content) {
        if (!content.match(/^https?:\/\//i)) {
            popup.className = popup.className.replace(/\bloading\b/, '');
            popup.innerHTML = content;
            return;
        }

        callAjax(content, function (responseText) {
            popup.className = popup.className.replace(/\bloading\b/, '');
            popup.innerHTML = responseText;
            centerPopup();
        });

        popup.className += ' loading';
    }

    function showCloseButton() {
        closeButton = document.createElement('DIV');
        closeButton.id = 'popup-close';
        closeButton.addEventListener('click', hidePopupAndOverlayIfShown);

        document.body.appendChild(closeButton);
    }

    function showOverlay() {
        overlay = document.createElement('DIV');
        overlay.id = 'modal-box-overlay';

        document.body.appendChild(overlay);
    }

    function showPopup(content) {
        popup = document.createElement('DIV');
        popup.id = 'modal-popup';

        processContent(content);

        document.body.appendChild(popup);
    }

    return function (content) {
        if (typeof content !== 'string' || content.length === 0) {
            return;
        }

        hidePopupAndOverlayIfShown();
        showOverlay();
        showPopup(content);
        showCloseButton();
        centerPopup();
    }
});
