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
      if (data && data.success && data.user) {
        var u = data.user;
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
          // Councilor / User -> Redirect to User Portal
          localStorage.setItem('user_logged_in', 'true');
          localStorage.setItem('current_user', JSON.stringify(u));
          window.location.href = '../users/user_dashboard.php?username=' + encodeURIComponent(u.username) + '&name=' + encodeURIComponent(u.name || '') + '&email=' + encodeURIComponent(u.email || '');
        }
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
})();
