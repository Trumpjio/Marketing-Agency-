/* Newsletter signup: posts to admin/subscribe.php. Used by any form with id="op-subscribe". */
(function () {
  var form = document.getElementById('op-subscribe');
  var msg = document.getElementById('op-subscribe-msg');
  if (!form || !msg) return;
  form.addEventListener('submit', function (e) {
    e.preventDefault();
    msg.textContent = '';
    msg.style.color = 'var(--muted)';
    fetch('admin/subscribe.php', { method: 'POST', body: new FormData(form) })
      .then(function (res) { return res.json().then(function (data) { return { ok: res.ok, data: data }; }); })
      .then(function (r) {
        if (r.ok && r.data.ok) {
          form.querySelector('input[name=email]').value = '';
          msg.textContent = r.data.alreadySubscribed ? 'Already on the list. Thank you.' : 'Thanks. We will email you when the next memo goes out.';
          msg.style.color = '#059669';
        } else {
          msg.textContent = r.data.error || 'Something went wrong. Please try again.';
          msg.style.color = '#dc2626';
        }
      })
      .catch(function () {
        msg.textContent = 'Could not reach the server. Please try again.';
        msg.style.color = '#dc2626';
      });
  });
})();
