define(function () {

    if (!Element.prototype.matches) {
        // See https://developer.mozilla.org/en-US/docs/Web/API/Element.matches
        Element.prototype.matches = Element.prototype.msMatchesSelector || Element.prototype.mozMatchesSelector || Element.prototype.webkitMatchesSelector || Element.prototype.oMatchesSelector
    }

    // IE10 dataset polyfill
    // From https://gist.githubusercontent.com/brettz9/4093766/raw/ba31a05e7ce21af67c6cafee9b3f439c86e95b01/html5-dataset.js
    if (!document.documentElement.dataset &&
            // FF is empty while IE gives empty object
        (!Object.getOwnPropertyDescriptor(Element.prototype, 'dataset') || !Object.getOwnPropertyDescriptor(Element.prototype, 'dataset').get)
    ) {
        var propDescriptor = {
            enumerable: true,
            get: function () {
                'use strict';
                var i,
                    that = this,
                    HTML5_DOMStringMap,
                    attrVal, attrName, propName,
                    attribute,
                    attributes = this.attributes,
                    attsLength = attributes.length,
                    toUpperCase = function (n0) {
                        return n0.charAt(1).toUpperCase();
                    },
                    getter = function () {
                        return this;
                    },
                    setter = function (attrName, value) {
                        return (typeof value !== 'undefined') ?
                            this.setAttribute(attrName, value) :
                            this.removeAttribute(attrName);
                    };
                try { // Simulate DOMStringMap w/accessor support
                    // Test setting accessor on normal object
                    ({}).__defineGetter__('test', function () {
                    });
                    HTML5_DOMStringMap = {};
                }
                catch (e1) { // Use a DOM object for IE8
                    HTML5_DOMStringMap = document.createElement('div');
                }
                for (i = 0; i < attsLength; i++) {
                    attribute = attributes[i];
                    // Fix: This test really should allow any XML Name without
                    //         colons (and non-uppercase for XHTML)
                    if (attribute && attribute.name &&
                        (/^data-\w[\w\-]*$/).test(attribute.name)) {
                        attrVal = attribute.value;
                        attrName = attribute.name;
                        // Change to CamelCase
                        propName = attrName.substr(5).replace(/-./g, toUpperCase);
                        try {
                            Object.defineProperty(HTML5_DOMStringMap, propName, {
                                enumerable: this.enumerable,
                                get: getter.bind(attrVal || ''),
                                set: setter.bind(that, attrName)
                            });
                        }
                        catch (e2) { // if accessors are not working
                            HTML5_DOMStringMap[propName] = attrVal;
                        }
                    }
                }
                return HTML5_DOMStringMap;
            }
        };
        try {
            // FF enumerates over element's dataset, but not
            //   Element.prototype.dataset; IE9 iterates over both
            Object.defineProperty(Element.prototype, 'dataset', propDescriptor);
        } catch (e) {
            propDescriptor.enumerable = false; // IE8 does not allow setting to true
            Object.defineProperty(Element.prototype, 'dataset', propDescriptor);
        }
    }

    // Return true if any ancestor matches selector
    // Borrowed from ancestorMatches() from agave.js (MIT)
    var isAncestorOf = function (element, selector, includeSelf) {
        var parent = element.parentNode;
        if (includeSelf && element.matches(selector)) {
            return true
        }
        // While parents are 'element' type nodes
        // See https://developer.mozilla.org/en-US/docs/DOM/Node.nodeType
        while (parent && parent.nodeType && parent.nodeType === 1) {
            if (parent.matches(selector)) {
                return true
            }
            parent = parent.parentNode;
        }
        return false;
    };

    // Used to match select boxes to their style select partners
    var makeUUID = function () {
        return 'ss-xxxx-xxxx-xxxx-xxxx-xxxx'.replace(/x/g, function (c) {
            var r = Math.random() * 16 | 0, v = c == 'x' ? r : r & 0x3 | 0x8;
            return v.toString(16);
        });
    };

    return function (selector) {
        if (navigator.userAgent.match(/iPad|iPhone|Android/i)) {
            return;
        }

        var realSelect = document.querySelector(selector),
            realOptions = realSelect.children,
            selectedIndex = realSelect.selectedIndex,
            uuid = makeUUID(),
            styleSelectHTML = '<div class="style-select" aria-hidden="true" data-ss-uuid="' + uuid + '">';

        realSelect.setAttribute('data-ss-uuid', uuid);
        // Even though the element is display: none, a11y users should still see it.
        // According to http://www.w3.org/TR/wai-aria/states_and_properties#aria-hidden
        // some browsers may have bugs with this but future implementation may improve
        realSelect.setAttribute('aria-hidden', "false");

        // Build styled clones of all the real options
        var selectedOptionHTML = '';
        var optionsHTML = '<div class="ss-dropdown">';
        Array.prototype.map.call(realOptions, function (realOption, index) {
            var text = realOption.textContent,
                value = realOption.getAttribute('value') || '',
                cssClass = 'ss-option';

            if (index === selectedIndex) {
                // Mark first item as selected-option - this is where we store state for the styled select box
                // aria-hidden=true so screen readers ignore the styles selext box in favor of the real one (which is visible by default)
                selectedOptionHTML = '<div class="ss-selected-option" tabindex="0" data-value="' + value + '">' + text + '</div>'
            }

            if (realOption.disabled) {
                cssClass += ' disabled';
            }

            optionsHTML += '<div class="' + cssClass + '" data-value="' + value + '">' + text + '</div>';
        });
        optionsHTML += '</div>';
        styleSelectHTML += selectedOptionHTML += optionsHTML += '</div>';
        realSelect.insertAdjacentHTML('afterend', styleSelectHTML);

        var styledSelect = document.querySelector('.style-select[data-ss-uuid="' + uuid + '"]');
        var styleSelectOptions = styledSelect.querySelectorAll('.ss-option');
        var selectedOption = styledSelect.querySelector('.ss-selected-option');

        var changeRealSelectBox = function (newValue, newLabel) {
            // Close styledSelect
            styledSelect.className = styledSelect.className.replace(/\bopen\b/i, '');

            // Update styled value
            selectedOption.textContent = newLabel;
            selectedOption.dataset.value = newValue;

            // Update the 'tick' that shows the option with the current value
            Array.prototype.map.call(styleSelectOptions, function (styleSelectOption) {
                if (styleSelectOption.dataset.value === newValue) {
                    styleSelectOption.className += ' ticked';
                } else {
                    styleSelectOption.className = styleSelectOption.className.replace(/\bticked\b/i, '');
                }
            });

            realSelect.value = newValue;

            var changeEvent = document.createEvent('HTMLEvents');
                changeEvent.initEvent('change', false, true);

            realSelect.dispatchEvent(changeEvent);
        };

        // Change real select box when a styled option is clicked
        Array.prototype.map.call(styleSelectOptions, function (unused, index) {
            var styleSelectOption = styleSelectOptions.item(index);

            if (styleSelectOption.className.match(/\bdisabled\b/)) {
                return;
            }

            styleSelectOption.addEventListener('click', function (event) {
                var newValue = event.target.getAttribute('data-value'),
                    newLabel = event.target.textContent;

                changeRealSelectBox(newValue, newLabel)
            }, true);

            if (styleSelectOption.dataset.value === realSelect.value) {
                styleSelectOption.className += ' ticked';
            }
        });

        var closeAllStyleSelectsExceptGiven = function (exception) {
            Array.prototype.map.call(document.querySelectorAll('.style-select'), function (styleSelectEl) {
                if (styleSelectEl !== exception) {
                    styleSelectEl.className = styleSelectEl.className.replace(/\bopen\b/i, '');
                }
            });
        };

        var toggleStyledSelect = function (styledSelectBox) {
            if (!styledSelectBox.className.match(/\bopen\b/i)) {
                closeAllStyleSelectsExceptGiven(styledSelectBox);
                styledSelectBox.className += ' open';
                return;
            }
            styledSelectBox.className = styledSelectBox.className.replace(/\bopen\b/i, '');
        };

        // When a styled select box is clicked
        var styledSelectedOption = document.querySelector('.style-select[data-ss-uuid="' + uuid + '"] .ss-selected-option');
        styledSelectedOption.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();
            toggleStyledSelect(event.target.parentNode);
        }, true);

        // Clicking outside of the styled select box closes any open styled select boxes
        document.querySelector('body').addEventListener('click', function (event) {
            if (!isAncestorOf(event.target, '.style-select', true)) {
                closeAllStyleSelectsExceptGiven();
            }
        }, true);
    };
});
