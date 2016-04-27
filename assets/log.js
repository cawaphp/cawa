var loglevel = require('loglevel');

window.Cawa = window.Cawa || {};
if (!window.Cawa.Log) {

    var originalFactory = loglevel.methodFactory;
    loglevel.methodFactory = function (methodName, logLevel, loggerName)
    {
        var rawMethod = originalFactory(methodName, logLevel, loggerName);

        return function ()
        {
            var args = Array.from(arguments);
            args.unshift('background:darkred; color:#FFF;padding-left:2px;padding-right:2px;');
            args.unshift('%c ' + loggerName + " ");

            rawMethod.apply(this, args);
        };
    };

    window.Cawa.Log = loglevel;
}

module.exports = window.Cawa.Log;
