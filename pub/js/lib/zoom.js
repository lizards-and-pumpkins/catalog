define(function () {
    var dw, dh, rw, rh, lx, ly;

    var Zoom = function (element) {
        this.target = element;

        if (this.isOpen === undefined) {
            this._init();
        }
    };

    Zoom.prototype._init = function () {
        var self = this;

        this.target.classList.add('zoom');

        this._cache = {};
        this.link = {};
        this.image = {};

        this.flyout = document.createElement('div');
        this.flyout.className = 'zoom-flyout';

        this.notice = document.createElement('div');
        this.notice.className = 'zoom-notice';

        this.target.addEventListener('click', function (event) {
            event.preventDefault();
        }, true);

        this.target.addEventListener('mouseover', function (event) {
            if (!self.isOpen) {
                event.preventDefault();
                self._show(this, event);
            }
        }, true);

        this.target.addEventListener('mousemove', function (event) {
            if (self.isOpen) {
                event.preventDefault();
                self._move(event);
            }
        }, true);

        this.target.addEventListener('mouseout', function (event) {
            if (self.isOpen) {
                event.preventDefault();
                self.hide();
            }
        }, true);
    };

    Zoom.prototype._show = function (link, e) {
        var w1, h1, w2, h2;
        var self = this;

        // Swap image
        if (link.href !== this.link.href) {
            this.link = link;
            this.image = link.querySelector('img');

            this._swap(link.href, function () {
                self._show(link, e);
            });

            return;
        }

        this.target.appendChild(this.flyout);

        // Find offset relative to viewport
        this.offset = (function (element) {

            var parent = element.offsetParent;
            var offset = {
                top: element.offsetTop,
                left: element.offsetLeft
            };

            while (parent.nodeName.toLocaleLowerCase() !== 'body') {
                offset.top += parent.offsetTop;
                offset.left += parent.offsetLeft;
                parent = parent.offsetParent;
            }

            return offset;
        })(this.target);

        w1 = this.image.clientWidth;
        h1 = this.image.clientHeight;

        w2 = this.flyout.clientWidth;
        h2 = this.flyout.clientHeight;

        dw = this.zoom.clientWidth - w2;
        dh = this.zoom.clientHeight - h2;

        rw = dw / w1;
        rh = dh / h1;

        this.isOpen = true;

        this._move(e);
    };

    Zoom.prototype._swap = function (href, callback) {
        var self = this;

        if (this.zoom) {
            this.flyout.removeChild(this.zoom);
        }

        var swap = function () {
            self.zoom = self.flyout.appendChild(self._cache[href]);
            callback();
        };

        if (this._cache[href]) {
            swap();
        } else {
            this._load(href, swap);
        }
    };

    Zoom.prototype._load = function (href, callback) {
        var self = this;
        var zoomedImage = new Image();

        this.notice.innerHTML = 'Loading';
        this.notice = this.target.appendChild(this.notice);

        zoomedImage.onload = function () {
            self._cache[href] = zoomedImage;
            self.notice = self.target.removeChild(self.notice);

            callback();
        };

        zoomedImage.style.position = 'absolute';
        zoomedImage.src = href;
    };

    Zoom.prototype._move = function (event) {
        lx = event.pageX || (event.clientX + document.body.scrollTop) || lx;
        ly = event.pageY || (event.clientY + document.body.scrollLeft) || ly;

        var pt = ly - this.offset.top;
        var pl = lx - this.offset.left;
        var xt = pt * rh;
        var xl = pl * rw;

        xt = (xt > dh) ? dh : xt;
        xl = (xl > dw) ? dw : xl;

        if (xl > 0 && xt > 0) {
            this.zoom.style.top = '' + (Math.ceil(xt) * -1) + 'px';
            this.zoom.style.left = '' + (Math.ceil(xl) * -1) + 'px';
        }
    };

    Zoom.prototype.hide = function () {
        if (this.isOpen) {
            this.flyout = this.target.removeChild(this.flyout);
            this.isOpen = false;
        }
    };

    return Zoom;
});
