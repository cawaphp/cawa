require([
    "jquery"
], function($)
{
    var log = require("log").getLogger("Cawa Request");

    var Request = function () {
        /**
         * @callback requestSuccess
         * @param {XMLHttpRequest} xhr
         * @param {Object} data
         */

        /**
         * @param {String} uri
         * @param {requestSuccess} callback
         * @param {String} method
         * @param {Object} data
         * @param {Object=} options
         */
        function send(uri, callback, method, data, options)
        {
            var beforeSend = options.beforeSend;
            delete options.beforeSend;

            options = $.extend(options === undefined ? {} : options, {
                url: uri,
                type: method === undefined ? "GET" : method,
                dataType: "json",
                beforeSend: function (xhr)
                {
                    if (beforeSend) {
                        beforeSend(xhr);
                    }

                    xhr.url = uri;
                    $(document).trigger("before.request", [xhr]);
                }
            });

            log.info("CawaRequest", uri, "with method",  options.type, "data", data);

            if (data instanceof FormData) {
                options.processData = false;
                options.contentType = false;

                options.xhr = function() {
                    var uploadXhr = $.ajaxSettings.xhr();
                    if(uploadXhr.upload){
                        uploadXhr.upload.addEventListener('progress',function(event)
                        {
                            $(document).trigger("progress.request", [event]);
                        }, false);

                    }
                    return uploadXhr;
                };
            }

            if (data !== undefined) {
                options.data = data;
            }

            var complete = null,
                fail = null,
                always = null;

            if (typeof callback === "function") {
                complete = callback;
            } else if (typeof callback === "object") {
                complete = callback.complete;
                fail = callback.fail;
                always = callback.always;
            }

            $.ajax(options)
                .done(function (result, textStatus, xhr)
                {
                    xhr.url = this.url;
                    if (complete) {
                        $(document).oneFirst("complete.request", complete);
                    }

                    $(document).trigger("complete.request", [
                        xhr,
                        result
                    ]);
                })
                .fail(function (xhr, textStatus, errorThrown)
                {
                    if (textStatus === "abort") {
                        return true;
                    }

                    if (fail) {
                        $(document).oneFirst("error.request", fail);
                    }

                    $(document).trigger("error.request", [
                        xhr,
                        errorThrown
                    ]);
                })
                .always(function (data, textStatus, xhr)
                {
                    if (always) {
                        $(document).oneFirst("finally.request", always);
                    }

                    if (textStatus === 'success') {
                        $(document).trigger("finally.request", [
                            xhr,
                            data
                        ]);
                    } else {
                        $(document).trigger("finally.request", [
                            data,
                            xhr
                        ]);
                    }
                });
        }

        function form(form, callback)
        {
            var method = form.attr('method');
            var uri = form.attr('action');

            if (!uri) {
                uri = document.location.href;
            }

            if (!method) {
                method = "POST";
            }

            var formData;
            if (method === "POST") {
                formData = new FormData(form[0]);
            } else {
                formData = form.serialize();
            }

            send(uri, callback,  method, formData);
        }

        return {
            send: send,
            form: form
        };
    };

    window.Cawa = window.Cawa || {};
    window.Cawa.Request = window.Cawa.Request || new Request();

    module.exports = window.Cawa.Request;
});

