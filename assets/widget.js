var $ = require("jquery");

/**
 * Base widget
 */
$.widget("cawa.widget",
{
    _createWidget: function ()
    {
        $.Widget.prototype._createWidget.apply(this, arguments);
        this._trigger('init');
    },

    _destroy: function ()
    {
        this._destroy();
        $.Widget.prototype.destroy.apply(this, arguments);
    },

    _getCreateOptions: function ()
    {
        var options = {};

        options = $.extend(true, {}, this.options);

        // all data-key camelCase
        $.each(this.element.data(), function (key, value) {
            if (typeof value !== 'function' && typeof value !== 'object') {
                options[$.camelCase(key)] = value;
            }

        });

        // <script type="application/json"></script> just next to current element
        $.extend(true, options, this._scriptOptions.apply(this, [this.element]));

        // _initOptions: function() method on widget
        if (typeof this._initOptions === 'function') {
            $.extend(true, options, this._initOptions.apply(this, [options]));
        }

        return options;
    },

    _scriptOptions: function(elem)
    {
        var options = {};
        var scriptElement;

        scriptElement = elem.is('script') ? elem : elem.nextAll('script[type="application/json"]').first();
        if (scriptElement.length > 0) {
            try {
                options = JSON.parse(scriptElement.html());
            } catch (exception) {
                console.log(scriptElement.html());
                throw('Unable to parse widget options for element ' + exception);
            }
        }

        return options;
    },

    enhanceWithin: function (target)
    {
        var self = this;

        if (!self.options.initSelector) {
            self.options.initSelector = "." + self.namespace + "-" + self.widgetName;
        }

        if (self.options.initSelector instanceof $) {
            self.enhance(self.options.initSelector);
        } else if (self.options.initSelector.indexOf('.') === 0) {
            self.enhance(target.getElementsByClassName(self.options.initSelector.slice(1)));
        } else {
            self.enhance($(self.options.initSelector, $(target)));
        }
    },

    enhance: function (targets)
    {
        var self = this;
        var elements = $(targets);
        elements[self.widgetName]();
    },

    raise: function (msg)
    {
        throw "[" + this.widgetName + "] " + msg;
    }
});

/**
 * Initialize all cawa event
 */
$(document).bind("ready cw.refresh", function (event)
{
    $.each($.cawa, function (key, current)
    {
        if (current.prototype.widgetName !== "widget") {
            $.cawa[key].prototype.enhanceWithin(event.target);
        }
    });
});
