require([
    "jquery",
    "moment"
], function($, moment)
{
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
     *
     * @param {String} url
     */
    $.fn.goto = function(url)
    {
        document.location.href = url;
    };

    /**
     * Control that moment is init
     */
    if (moment.locale($.locale()) !== $.locale()) {
        throw "Can't set moment global locale to '" + $.locale() + "'";
    }
});

