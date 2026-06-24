(function (window) {
    'use strict';

    if (!window.PriceDisplayPolicy || PriceDisplayPolicy.showStockIndexPrices() || typeof window.bubble !== 'function')
        return;

    var renderBubble = window.bubble;
    window.bubble = function () {
        var args = Array.prototype.slice.call(arguments);
        args[2] = 0;
        return renderBubble.apply(this, args);
    };
})(window);
