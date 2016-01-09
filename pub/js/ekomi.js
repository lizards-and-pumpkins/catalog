define(['lib/domReady'], function (domReady) {
    domReady(function () {
        if (typeof eKomiIntegrationConfig === 'undefined') {
            return;
        }

        eKomiIntegrationConfig.map(function (config) {
            var script = document.createElement('SCRIPT');
            script.defer = true;
            script.src = '//connect.ekomi.de/integration_1369656051/' + config.certId + '.js';

            document.getElementsByTagName('head')[0].appendChild(script);
        });
    });
});
