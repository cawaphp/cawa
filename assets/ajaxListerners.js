/**
 * Capture all XMLHttpRequest to add an global event
 */
var XMLHttpRequestListener = new Object();

// Added for IE support
if (typeof XMLHttpRequest === "undefined") {
    XMLHttpRequest = function ()
    {
        try {
            return new ActiveXObject("Msxml2.XMLHTTP.6.0");
        } catch (e) {
        }

        try {
            return new ActiveXObject("Msxml2.XMLHTTP.3.0");
        } catch (e) {
        }

        try {
            return new ActiveXObject("Microsoft.XMLHTTP");
        } catch (e) {
        }

        throw new Error("This browser does not support XMLHttpRequest.");
    };
}

XMLHttpRequestListener.originalSend = XMLHttpRequest.prototype.send;

XMLHttpRequest.prototype.send = function (data)
{
    XMLHttpRequestListener.originalSend.apply(this, arguments);
    $(document).trigger('ajaxQuery', this);
};

