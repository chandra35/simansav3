(function () {
  function normalize(text) {
    return String(text || '').toLowerCase().trim();
  }

  function escapeHtml(text) {
    return String(text || '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function friendlyError(raw) {
    var text = normalize(raw);
    if (!text) return null;

    var rules = [
      {
        match: ['password hotspot belum aman', 'reset password simansa'],
        title: 'Password perlu diperbarui',
        message: 'Silakan atur ulang password Hotspot melalui SIMANSA sebelum mencoba kembali.'
      },
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
        message: 'Username atau password belum sesuai. Periksa kembali ID akun dan password Anda.'
      },
      {
        match: ['radius timeout', 'radius is not responding', 'authentication server'],
        title: 'Server autentikasi belum merespons',
        message: 'Router belum mendapat jawaban dari RADIUS. Coba ulang beberapa saat lagi.'
      },
      {
        match: ['chap missing', 'web browser did not send challenge response'],
        title: 'Sesi login kedaluwarsa',
        message: 'Muat ulang halaman login, lalu coba masuk kembali.',
        reload: true
      },
      {
        match: ['unknown host'],
        title: 'Koneksi perangkat berubah',
        message: 'Gateway tidak lagi mengenali sesi perangkat ini. Sambungkan ulang Wi-Fi, lalu buka portal dari notifikasi jaringan.',
        reload: true
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

    target.className = 'hotspot-modal is-visible';
    target.setAttribute('role', 'alertdialog');
    target.setAttribute('aria-modal', 'true');
    target.setAttribute('aria-labelledby', 'hotspot-error-title');
    target.innerHTML = '<div class="hotspot-modal-card"><div class="result-icon error-icon" aria-hidden="true">!</div><span class="result-kicker error-kicker">LOGIN GAGAL</span><strong id="hotspot-error-title">' + escapeHtml(mapped.title) + '</strong><span>' + escapeHtml(mapped.message) + '</span><small>Informasi router: ' + escapeHtml(raw) + '</small><button type="button" class="button primary" data-error-close>Coba Lagi</button></div>';
    document.body.classList.add('modal-open');
    var close = target.querySelector('[data-error-close]');
    if (close) close.addEventListener('click', function () {
      if (mapped.reload) {
        window.location.reload();
        return;
      }
      target.classList.remove('is-visible');
      document.body.classList.remove('modal-open');
      var username = document.getElementById('username');
      if (username) username.focus();
    });
  }

  function setupPasswordToggle() {
    var button = document.querySelector('[data-password-toggle]');
    var input = document.getElementById('password');
    if (!button || !input) return;

    button.addEventListener('click', function () {
      var visible = input.type === 'text';
      input.type = visible ? 'password' : 'text';
      button.textContent = visible ? 'Lihat' : 'Tutup';
      button.setAttribute('aria-label', visible ? 'Tampilkan password' : 'Sembunyikan password');
      button.setAttribute('aria-pressed', visible ? 'false' : 'true');
      input.focus();
    });
  }

  function setupRememberedUsername() {
    var form = document.getElementById('hotspot-login-form');
    var username = document.getElementById('username');
    var password = document.getElementById('password');
    var remember = document.getElementById('rememberUsername');
    var storageKey = 'simansa.hotspot.rememberedUsername';
    if (!form || !username || !remember) return;

    try {
      var savedUsername = window.localStorage.getItem(storageKey) || '';
      if (savedUsername) {
        username.value = savedUsername;
        remember.checked = true;
        if (password) password.focus();
      }
    } catch (error) {
      remember.disabled = true;
    }

    form.addEventListener('submit', function () {
      try {
        if (remember.checked && username.value.trim()) {
          window.localStorage.setItem(storageKey, username.value.trim());
        } else {
          window.localStorage.removeItem(storageKey);
        }
      } catch (error) {
        // Captive portal tertentu menonaktifkan storage; login tetap dilanjutkan.
      }
    }, true);
  }

  document.addEventListener('DOMContentLoaded', function () {
    renderError();
    setupPasswordToggle();
    setupRememberedUsername();
  });
}());
