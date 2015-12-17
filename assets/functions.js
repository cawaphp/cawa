var $ = require("jquery");
var moment = require("moment");

/**
 * Current locale
 */
$.extend({
    _currentLocale: null,
    locale: function()
    {
        if ($._currentLocale) {
            return $._currentLocale;
        }

        $._currentLocale = $('meta[http-equiv=Content-Language]').attr("content");

        return $._currentLocale;
    }
});

/**
 * Simple url change that can be override
 */
$.extend({
    goto: function(url)
    {
        document.location.href = url;
    }
});

/**
 * Init
 */
$(document).bind("ready", function (event) {

    // set global moment locale
    if (moment.locale($.locale()) !== $.locale()) {
        throw "Can't set moment global locale to '" + $.locale() + "'";
    }

});
