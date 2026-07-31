(function () {
  function normalize(text) {
    return String(text || '').toLowerCase().trim();
  }

  function friendlyError(raw) {
    var text = normalize(raw);
    if (!text) return null;

    var rules = [
      {
        match: ['simultaneous session limit', 'no more sessions', 'already logged in'],
        title: 'Akun sedang dipakai',
        message: 'Akun ini masih aktif di perangkat lain. Logout dari perangkat tersebut atau tunggu sesi sebelumnya berakhir.'
      },
      {
        match: ['user disabled', 'disabled'],
        title: 'Akun nonaktif',
        message: 'Akun hotspot ini sedang nonaktif. Hubungi admin jika akses masih dibutuhkan.'
      },
      {
        match: ['expired', 'validity', 'not valid'],
        title: 'Akun expired',
        message: 'Masa berlaku akun hotspot sudah habis.'
      },
      {
        match: ['uptime limit', 'traffic limit', 'limit reached'],
        title: 'Batas akses habis',
        message: 'Kuota atau durasi akses akun sudah mencapai batas.'
      },
      {
        match: ['invalid username or password', 'invalid password', 'not found', 'radius reject'],
        title: 'Login ditolak',
        message: 'Username atau password belum sesuai. Siswa menggunakan NISN sebagai username.'
      },
      {
        match: ['radius timeout', 'radius is not responding', 'authentication server'],
        title: 'Server autentikasi belum merespons',
        message: 'Router belum mendapat jawaban dari RADIUS. Coba ulang beberapa saat lagi.'
      },
      {
        match: ['chap missing', 'web browser did not send challenge response'],
        title: 'Sesi login kedaluwarsa',
        message: 'Muat ulang halaman login, lalu coba masuk kembali.'
      }
    ];

    for (var i = 0; i < rules.length; i += 1) {
      for (var j = 0; j < rules[i].match.length; j += 1) {
        if (text.indexOf(rules[i].match[j]) !== -1) return rules[i];
      }
    }

    return {
      title: 'Login belum berhasil',
      message: 'Router menolak akses untuk akun ini.'
    };
  }

  function renderError() {
    var target = document.querySelector('[data-hotspot-error]');
    if (!target) return;

    var raw = target.getAttribute('data-hotspot-error') || '';
    if (raw.indexOf('$(') === 0) return;

    var mapped = friendlyError(raw);
    if (!mapped) return;

    target.classList.add('is-visible');
    target.innerHTML = '<strong>' + mapped.title + '</strong><span>' + mapped.message + '</span><small>Detail teknis: ' + raw + '</small>';
  }

  document.addEventListener('DOMContentLoaded', renderError);
}());
