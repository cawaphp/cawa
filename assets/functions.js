require([
    "jquery",
    "moment"
], function($, moment)
{

    $.extend({
        /**
         * Current locale
         */
        _currentLocale: null,
        locale: function()
        {
            if ($._currentLocale) {
                return $._currentLocale;
            }

            $._currentLocale = $('meta[http-equiv=Content-Language]').attr("content");

            return $._currentLocale;
        },

        /**
         * @param {Object} object
         * @param {Function} callback
         */
        forEach: function(object, callback)
        {
            for (var key in object) {
                if (object.hasOwnProperty(key)) {
                    if (callback(object[key], key) === false) {
                        break;
                    }
                }
            }
        },

        /**
         * @param {Object} object
         * @returns  {Object}
         */
        invert: function(object)
        {
            var ret = {};

            $.forEach(object, function(value, key)
            {
                ret[value] = key;
            });

            return ret;
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

