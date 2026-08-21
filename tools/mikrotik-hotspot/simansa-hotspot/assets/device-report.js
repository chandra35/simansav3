(function () {
  var root = document.body;
  if (!root) return;

  var endpoint = root.getAttribute('data-device-report-url');
  var username = root.getAttribute('data-hotspot-username');
  var mac = root.getAttribute('data-hotspot-mac');
  var ip = root.getAttribute('data-hotspot-ip');
  if (!endpoint || !username || username.indexOf('$(') === 0 || !mac || mac.indexOf('$(') === 0) return;

  function fallbackModel(userAgent) {
    var match = String(userAgent || '').match(/Android[^;)]*;\s*([^;)]+?)(?:\s+Build\/[^;)]*)?[;)]/i);
    if (match && match[1]) return match[1].trim();
    if (/iPad/i.test(userAgent)) return 'iPad';
    if (/iPhone/i.test(userAgent)) return 'iPhone';
    return '';
  }

  function send(details) {
    var payload = new URLSearchParams();
    payload.set('username', username);
    payload.set('mac', mac);
    if (ip && ip.indexOf('$(') !== 0) payload.set('ip', ip);
    payload.set('model', details.model || fallbackModel(navigator.userAgent));
    payload.set('platform', details.platform || navigator.platform || '');
    payload.set('platform_version', details.platformVersion || '');
    payload.set('architecture', details.architecture || '');
    payload.set('brands', JSON.stringify(details.fullVersionList || details.brands || []));

    if (navigator.sendBeacon && navigator.sendBeacon(endpoint, payload)) return;
    window.fetch(endpoint, { method: 'POST', body: payload, mode: 'no-cors', keepalive: true }).catch(function () {});
  }

  var uaData = navigator.userAgentData;
  if (uaData && uaData.getHighEntropyValues) {
    uaData.getHighEntropyValues(['model', 'platform', 'platformVersion', 'architecture', 'fullVersionList'])
      .then(send)
      .catch(function () { send({ platform: uaData.platform, brands: uaData.brands }); });
  } else {
    send({});
  }
}());
