// assets/login.js — Role-based Login Router & Authenticator
(function () {
  window.togglePasswordVisibility = function (inputId, btn) {
    var input = typeof inputId === 'string' ? document.getElementById(inputId) : inputId;
    if (!input) return;
    var icon = (btn && btn.querySelector) ? btn.querySelector('i') : (btn || document.getElementById('passwordToggleIcon'));
    if (input.type === 'password') {
      input.type = 'text';
      if (icon) {
        icon.className = 'bi bi-eye-slash';
      }
      if (btn) btn.setAttribute('title', 'Hide Password');
    } else {
      input.type = 'password';
      if (icon) {
        icon.className = 'bi bi-eye';
      }
      if (btn) btn.setAttribute('title', 'Show Password');
    }
  };

  window.handleLoginFormSubmit = function (e) {
    if (e) e.preventDefault();

    var usernameEl = document.getElementById('username') || document.getElementById('loginUsername');
    var passwordEl = document.getElementById('password') || document.getElementById('loginPassword');

    if (!usernameEl || !passwordEl) return true;

    var username = usernameEl.value.trim();
    var password = passwordEl.value.trim();

    if (!username || !password) {
      alert('Please enter your username/email and password.');
      return false;
    }

    // 1. Check localStorage provisioned users fallback (for offline or local testing)
    var localUsers = [];
    try {
      localUsers = JSON.parse(localStorage.getItem('legislative_system_users') || '[]');
    } catch(err) { localUsers = []; }

    var matchedLocal = localUsers.find(function(u) {
      if (!u) return false;
      var matchUser = u.username && u.username.toLowerCase() === username.toLowerCase();
      var matchEmail = u.email && u.email.toLowerCase() === username.toLowerCase();
      return matchUser || matchEmail;
    });

    // 3. Send API AJAX login to backend auth/login.php
    var formData = new FormData();
    formData.append('api_login', '1');
    formData.append('username', username);
    formData.append('password', password);

    try {
      var savedAdmin = JSON.parse(localStorage.getItem('admin_profile_data') || '{}');
      var curr = JSON.parse(localStorage.getItem('current_user') || '{}');
      var savedStaff = JSON.parse(localStorage.getItem('staff_profile_data') || '{}');
      if (username.toLowerCase().includes('admin')) {
        if (savedAdmin.name) formData.append('display_name', savedAdmin.name);
        else if (curr.name && curr.name !== 'System Administrator' && curr.name !== 'Admin') formData.append('display_name', curr.name);
      } else if (username.toLowerCase().includes('staff')) {
        if (savedStaff.name) formData.append('display_name', savedStaff.name);
        else if (curr.name && curr.name !== 'Staff Officer' && curr.name !== 'Staff') formData.append('display_name', curr.name);
      } else if (curr.name) {
        formData.append('display_name', curr.name);
      }
    } catch(e) {}

    var authUrl = window.location.pathname.includes('/auth/') ? 'login.php' : '../auth/login.php';

    fetch(authUrl, {
      method: 'POST',
      body: formData
    })
    .then(function (res) { return res.json(); })
    .then(function (data) {
      // ── STEP 1: Two-Factor Verification Required ──
      if (data && data.step === 'otp_required') {
        window.pendingOtpUsername = data.username || username;
        window.showOtpScreen(data);
        return;
      }

      // ── Direct Login Success (if OTP not enabled for role) ──
      if (data && data.success && data.user) {
        window.completeLoginSuccess(data.user, username);
      } else {
        // Fallback for provisioned accounts matched in localStorage
        if (matchedLocal) {
          var locRole = (matchedLocal.role || 'Staff').toLowerCase();
          var locName = matchedLocal.name || matchedLocal.username || 'User';
          sessionStorage.setItem('pending_login_audit', locName);
          if (locRole === 'staff' || locRole === 'legislative staff') {
            localStorage.setItem('staff_logged_in', 'true');
            localStorage.removeItem('admin_logged_in');
            localStorage.setItem('current_user', JSON.stringify(matchedLocal));
            window.location.href = '../staff/staff_dashboard.php';
          } else {
            localStorage.setItem('user_logged_in', 'true');
            localStorage.setItem('current_user', JSON.stringify(matchedLocal));
            window.location.href = '../users/user_dashboard.php?username=' + encodeURIComponent(matchedLocal.username) + '&name=' + encodeURIComponent(matchedLocal.name || '') + '&email=' + encodeURIComponent(matchedLocal.email || '');
          }
        } else {
          alert(data.error || 'Invalid credentials or account does not exist.');
        }
      }
    })
    .catch(function () {
      if (matchedLocal) {
        var locRole = (matchedLocal.role || 'Staff').toLowerCase();
        var locName = matchedLocal.name || matchedLocal.username || 'User';
        sessionStorage.setItem('pending_login_audit', locName);
        if (locRole === 'staff' || locRole === 'legislative staff') {
          localStorage.setItem('staff_logged_in', 'true');
          localStorage.removeItem('admin_logged_in');
          localStorage.setItem('current_user', JSON.stringify(matchedLocal));
          window.location.href = '../staff/staff_dashboard.php';
        } else {
          localStorage.setItem('user_logged_in', 'true');
          localStorage.setItem('current_user', JSON.stringify(matchedLocal));
          window.location.href = '../users/user_dashboard.php?username=' + encodeURIComponent(matchedLocal.username) + '&name=' + encodeURIComponent(matchedLocal.name || '') + '&email=' + encodeURIComponent(matchedLocal.email || '');
        }
      } else {
        alert('Unable to connect to server. Please check your credentials and try again.');
      }
    });

    return false;
  };

  // ── OTP UI Control & Handlers ──
  var otpTimerInterval = null;
  var resendTimerInterval = null;

  window.showOtpScreen = function (data) {
    var loginForm = document.getElementById('loginForm');
    var otpSection = document.getElementById('otpSection');
    var maskedEmailEl = document.getElementById('otpMaskedEmail');
    var alertBox = document.getElementById('otpAlertBox');
    var input = document.getElementById('otpCodeInput');

    if (loginForm) loginForm.style.display = 'none';
    if (otpSection) otpSection.style.display = 'block';

    if (maskedEmailEl) {
      maskedEmailEl.textContent = data.email || data.full_email || 'your email';
    }

    if (alertBox) {
      if (data.dev_hint) {
        alertBox.style.display = 'flex';
        alertBox.style.background = '#f0fdf4';
        alertBox.style.border = '1px solid #bbf7d0';
        alertBox.style.color = '#15803d';
        alertBox.innerHTML = '<i class="bi bi-info-circle-fill"></i><span>Security PIN generated: <strong>' + data.dev_hint + '</strong></span>';
      } else {
        alertBox.style.display = 'none';
      }
    }

    if (input) {
      input.value = '';
      input.focus();
    }

    window.startOtpTimer(600); // 10 minutes
    window.startResendCooldown(60); // 60 seconds
  };

  window.backToPasswordLogin = function () {
    clearInterval(otpTimerInterval);
    clearInterval(resendTimerInterval);
    var loginForm = document.getElementById('loginForm');
    var otpSection = document.getElementById('otpSection');
    if (loginForm) loginForm.style.display = 'block';
    if (otpSection) otpSection.style.display = 'none';
  };

  window.startOtpTimer = function (totalSeconds) {
    clearInterval(otpTimerInterval);
    var timerDisplay = document.getElementById('otpTimerDisplay');
    var remaining = totalSeconds;

    function update() {
      var mins = Math.floor(remaining / 60);
      var secs = remaining % 60;
      if (timerDisplay) {
        timerDisplay.textContent = (mins < 10 ? '0' : '') + mins + ':' + (secs < 10 ? '0' : '') + secs;
      }
      if (remaining <= 0) {
        clearInterval(otpTimerInterval);
        var alertBox = document.getElementById('otpAlertBox');
        if (alertBox) {
          alertBox.style.display = 'flex';
          alertBox.style.background = '#fef2f2';
          alertBox.style.border = '1px solid #fecaca';
          alertBox.style.color = '#dc2626';
          alertBox.innerHTML = '<i class="bi bi-exclamation-triangle-fill"></i><span>Verification code expired. Please click Resend Code.</span>';
        }
      }
      remaining--;
    }
    update();
    otpTimerInterval = setInterval(update, 1000);
  };

  window.startResendCooldown = function (seconds) {
    clearInterval(resendTimerInterval);
    var resendBtn = document.getElementById('otpResendBtn');
    if (!resendBtn) return;
    var cd = seconds;
    resendBtn.disabled = true;

    function updateCd() {
      if (cd > 0) {
        resendBtn.textContent = 'Resend (' + cd + 's)';
        cd--;
      } else {
        clearInterval(resendTimerInterval);
        resendBtn.disabled = false;
        resendBtn.textContent = 'Resend code';
      }
    }
    updateCd();
    resendTimerInterval = setInterval(updateCd, 1000);
  };

  window.handleOtpFormSubmit = function (e) {
    if (e) e.preventDefault();
    var input = document.getElementById('otpCodeInput');
    var submitBtn = document.getElementById('otpSubmitBtn');
    var alertBox = document.getElementById('otpAlertBox');

    if (!input) return false;
    var code = input.value.trim();
    if (code.length < 6) {
      if (alertBox) {
        alertBox.style.display = 'flex';
        alertBox.style.background = '#fef2f2';
        alertBox.style.border = '1px solid #fecaca';
        alertBox.style.color = '#dc2626';
        alertBox.innerHTML = '<i class="bi bi-exclamation-triangle-fill"></i><span>Please enter all 6 digits.</span>';
      }
      return false;
    }

    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Verifying...';
    }

    var formData = new FormData();
    formData.append('verify_otp', '1');
    formData.append('username', window.pendingOtpUsername || '');
    formData.append('otp_code', code);

    var authUrl = window.location.pathname.includes('/auth/') ? 'login.php' : '../auth/login.php';

    fetch(authUrl, {
      method: 'POST',
      body: formData
    })
    .then(function (res) { return res.json(); })
    .then(function (data) {
      if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.innerHTML = 'Verify &amp; Sign In <i class="bi bi-arrow-right ms-1"></i>';
      }

      if (data && data.success && data.user) {
        clearInterval(otpTimerInterval);
        clearInterval(resendTimerInterval);
        window.completeLoginSuccess(data.user, window.pendingOtpUsername);
      } else {
        if (alertBox) {
          alertBox.style.display = 'flex';
          alertBox.style.background = '#fef2f2';
          alertBox.style.border = '1px solid #fecaca';
          alertBox.style.color = '#dc2626';
          alertBox.innerHTML = '<i class="bi bi-exclamation-circle-fill"></i><span>' + (data.error || 'Invalid code.') + '</span>';
        }
        input.select();
      }
    })
    .catch(function () {
      if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.innerHTML = 'Verify &amp; Sign In <i class="bi bi-arrow-right ms-1"></i>';
      }
      alert('Verification request failed. Please check your connection.');
    });

    return false;
  };

  window.handleOtpResend = function () {
    var resendBtn = document.getElementById('otpResendBtn');
    var alertBox = document.getElementById('otpAlertBox');
    if (resendBtn) resendBtn.disabled = true;

    var formData = new FormData();
    formData.append('resend_otp', '1');
    formData.append('username', window.pendingOtpUsername || '');

    var authUrl = window.location.pathname.includes('/auth/') ? 'login.php' : '../auth/login.php';

    fetch(authUrl, {
      method: 'POST',
      body: formData
    })
    .then(function (res) { return res.json(); })
    .then(function (data) {
      window.startResendCooldown(60);
      window.startOtpTimer(600);

      if (alertBox) {
        alertBox.style.display = 'flex';
        alertBox.style.background = '#f0fdf4';
        alertBox.style.border = '1px solid #bbf7d0';
        alertBox.style.color = '#15803d';
        var msg = data.message || 'New verification code sent!';
        if (data.dev_hint) msg += ' (PIN: ' + data.dev_hint + ')';
        alertBox.innerHTML = '<i class="bi bi-check-circle-fill"></i><span>' + msg + '</span>';
      }
    })
    .catch(function () {
      window.startResendCooldown(15);
      alert('Could not resend code. Please try again.');
    });
  };

  // ── Auto-submit OTP when 6 digits are reached ──
  document.addEventListener('input', function (e) {
    if (e.target && e.target.id === 'otpCodeInput') {
      var val = e.target.value.replace(/\D/g, '');
      e.target.value = val;
      if (val.length === 6) {
        window.handleOtpFormSubmit();
      }
    }
  });

  window.completeLoginSuccess = function (u, username) {
    var role = (u.role || '').toLowerCase();
    var displayName = u.name || username;
    sessionStorage.setItem('pending_login_audit', displayName);

    if (role === 'admin' || role === 'administrator') {
      localStorage.setItem('admin_logged_in', 'true');
      localStorage.removeItem('staff_logged_in');
      localStorage.setItem('current_user', JSON.stringify(u));
      window.location.href = '../admin/admin_dashboard.php';
    } else if (role === 'staff' || role === 'legislative staff') {
      localStorage.setItem('staff_logged_in', 'true');
      localStorage.removeItem('admin_logged_in');
      localStorage.setItem('current_user', JSON.stringify(u));
      window.location.href = '../staff/staff_dashboard.php';
    } else {
      localStorage.setItem('user_logged_in', 'true');
      localStorage.setItem('current_user', JSON.stringify(u));
      window.location.href = '../users/user_dashboard.php?username=' + encodeURIComponent(u.username) + '&name=' + encodeURIComponent(u.name || '') + '&email=' + encodeURIComponent(u.email || '');
    }
  };
})();
