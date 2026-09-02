<!-- users/profile.php — User Profile Submodule -->
<section id="profileSection" class="content-section <?= ($active_section ?? '') !== 'profileSection' ? 'd-none' : '' ?>">

  <!-- Page Header -->
  <div class="mb-4">
    <h2 class="h4 fw-bold text-dark mb-1">User Profile</h2>
    <p class="text-muted small mb-0">Manage your municipal account details, profile picture, and credentials.</p>
  </div>

  <!-- Profile Header Card -->
  <div class="card border shadow-sm rounded-4 p-4 mb-4 bg-white">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
      <div class="d-flex align-items-center gap-3">
        <div class="position-relative">
          <div id="userProfileAvatarContainer"
            class="rounded-circle d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary flex-shrink-0 overflow-hidden border border-2 border-primary border-opacity-25 shadow-sm"
            style="width: 70px; height: 70px;">
            <img id="userProfileHeaderAvatarImg" src="" alt="User Profile" class="w-100 h-100 object-fit-cover d-none" />
            <i id="userProfileHeaderAvatarFallback" class="bi bi-person-fill" style="font-size: 2.2rem;"></i>
          </div>
          <button type="button" class="btn btn-primary btn-sm rounded-circle position-absolute bottom-0 end-0 p-0 d-flex align-items-center justify-content-center shadow"
            style="width: 26px; height: 26px;" onclick="document.getElementById('userAvatarFileInput').click()" title="Change Profile Picture">
            <i class="bi bi-camera-fill" style="font-size: 0.75rem;"></i>
          </button>
          <input type="file" id="userAvatarFileInput" accept="image/*" class="d-none" onchange="handleUserAvatarUpload(this)">
        </div>
        <div>
          <h3 class="h4 fw-bold text-dark mb-1" id="userProfileHeaderName">User</h3>
          <div class="text-secondary fw-medium" style="font-size: 0.92rem;">
            <i class="bi bi-at text-muted me-0.5"></i><span id="userProfileHeaderUsername">username</span> &bull; 
            <i class="bi bi-envelope text-muted ms-1 me-1"></i><span id="userProfileHeaderEmail">user@manila.gov.ph</span> &bull; 
            <i class="bi bi-award text-muted ms-1 me-1"></i><span id="userProfileHeaderRole">Councilor / Member</span>
          </div>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="d-flex flex-wrap align-items-center gap-2.5">
        <button type="button" class="btn btn-action-photo rounded-3 px-3.5 py-2 shadow-2xs d-inline-flex align-items-center gap-2"
          onclick="document.getElementById('userAvatarFileInput').click()">
          <i class="bi bi-camera text-primary fs-6"></i>
          <span>Change Photo</span>
        </button>
        <button type="button" id="removeUserAvatarBtn" class="btn btn-action-danger rounded-3 px-3.5 py-2 shadow-2xs d-inline-flex align-items-center gap-2 d-none"
          onclick="removeUserAvatar()" title="Remove Photo">
          <i class="bi bi-trash3 text-danger fs-6"></i>
          <span>Remove Photo</span>
        </button>
        <button type="button" class="btn btn-action-primary rounded-3 px-3.5 py-2 shadow-sm d-inline-flex align-items-center gap-2"
          onclick="toggleUserEditProfileForm(true)">
          <i class="bi bi-pencil-square fs-6"></i>
          <span>Edit Profile</span>
        </button>
        <button type="button" class="btn btn-action-warning rounded-3 px-3.5 py-2 shadow-2xs d-inline-flex align-items-center gap-2"
          data-bs-toggle="modal" data-bs-target="#changeUserPasswordModal">
          <i class="bi bi-key-fill fs-6" style="color: #d97706;"></i>
          <span>Change Password</span>
        </button>
      </div>
    </div>
  </div>

  <!-- Card: Account Details -->
  <div class="card border shadow-sm rounded-4 p-4 p-md-5 bg-white mb-4">
    <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-bottom">
      <div class="d-flex align-items-center gap-3">
        <div class="rounded-3 bg-primary bg-opacity-10 text-primary p-2.5 d-flex align-items-center justify-content-center"
          style="width: 44px; height: 44px;">
          <i class="bi bi-person-vcard fs-4"></i>
        </div>
        <div>
          <h3 class="h5 fw-bold text-dark mb-0">Account Details</h3>
          <p class="text-muted small mb-0 mt-0.5" style="font-size: 0.85rem;">Official councilor / user profile information and account credentials</p>
        </div>
      </div>
      <div id="userProfileEditActions" class="d-none">
        <button type="button" class="btn btn-light border px-3.5 py-2 rounded-3 me-2 fw-semibold"
          onclick="toggleUserEditProfileForm(false)">Cancel</button>
        <button type="button" class="btn btn-primary fw-semibold px-4 py-2 rounded-3 shadow-sm" onclick="saveUserProfileDetails()">
          <i class="bi bi-check-lg me-1"></i> Save Changes
        </button>
      </div>
    </div>

    <form id="userProfileForm" onsubmit="saveUserProfileDetails(); return false;">
      <div class="row g-4">
        <div class="col-12 col-md-6">
          <label class="form-label fw-semibold text-dark mb-2" style="font-size: 0.95rem;">Account Holder Name</label>
          <input type="text" id="userProfInputName" class="form-control form-control-lg bg-light py-2.5 px-3 fs-6 rounded-3"
            value="" readonly required style="font-size: 0.98rem;" placeholder="Enter name">
        </div>

        <div class="col-12 col-md-6">
          <label class="form-label fw-semibold text-dark mb-2" style="font-size: 0.95rem;">Username</label>
          <input type="text" id="userProfInputUsername" class="form-control form-control-lg bg-light py-2.5 px-3 fs-6 rounded-3"
            value="" readonly disabled style="font-size: 0.98rem;" placeholder="Username">
        </div>

        <div class="col-12 col-md-6">
          <label class="form-label fw-semibold text-dark mb-2" style="font-size: 0.95rem;">Official Email Address</label>
          <input type="email" id="userProfInputEmail" class="form-control form-control-lg bg-light py-2.5 px-3 fs-6 rounded-3"
            value="" readonly required style="font-size: 0.98rem;" placeholder="Email address">
        </div>

        <div class="col-12 col-md-6">
          <label class="form-label fw-semibold text-dark mb-2" style="font-size: 0.95rem;">Assigned Department / Office</label>
          <input type="text" id="userProfInputDept" class="form-control form-control-lg bg-light py-2.5 px-3 fs-6 rounded-3"
            value="" readonly required style="font-size: 0.98rem;" placeholder="Department / Office">
        </div>

        <div class="col-12 col-md-6">
          <label class="form-label fw-semibold text-dark mb-2" style="font-size: 0.95rem;">Account Role</label>
          <input type="text" id="userProfInputRole" class="form-control form-control-lg bg-light py-2.5 px-3 fs-6 rounded-3"
            value="Councilor" readonly disabled style="font-size: 0.98rem;">
        </div>

        <div class="col-12 col-md-6">
          <label class="form-label fw-semibold text-dark mb-2" style="font-size: 0.95rem;">Date Provisioned</label>
          <input type="text" id="userProfInputDate" class="form-control form-control-lg bg-light py-2.5 px-3 fs-6 rounded-3"
            value="" readonly disabled style="font-size: 0.98rem;">
        </div>
      </div>
    </form>
  </div>

</section>

<script>
function getUserProfileStorageKey() {
  try {
    const curr = JSON.parse(localStorage.getItem('current_user') || '{}');
    const u = curr.username || curr.id || 'default';
    return 'user_profile_data_' + u;
  } catch (e) {
    return 'user_profile_data_default';
  }
}

function toggleUserEditProfileForm(isEditing) {
  const editableIds = ['userProfInputName', 'userProfInputEmail', 'userProfInputDept'];
  const actions = document.getElementById('userProfileEditActions');

  editableIds.forEach(id => {
    const el = document.getElementById(id);
    if (el) {
      if (isEditing) {
        el.removeAttribute('readonly');
        el.classList.remove('bg-light');
        el.classList.add('bg-white');
      } else {
        el.setAttribute('readonly', 'readonly');
        el.classList.remove('bg-white');
        el.classList.add('bg-light');
      }
    }
  });

  if (actions) {
    if (isEditing) actions.classList.remove('d-none');
    else actions.classList.add('d-none');
  }

  if (isEditing && document.getElementById('userProfInputName')) {
    document.getElementById('userProfInputName').focus();
  }
}

function handleUserAvatarUpload(input) {
  if (input.files && input.files[0]) {
    const file = input.files[0];
    if (file.size > 5 * 1024 * 1024) {
      alert('Image file is too large. Please select an image under 5MB.');
      return;
    }
    const reader = new FileReader();
    reader.onload = function (e) {
      const dataUrl = e.target.result;
      saveUserAvatarData(dataUrl);
    };
    reader.readAsDataURL(file);
  }
}

function saveUserAvatarData(dataUrl) {
  try {
    const key = getUserProfileStorageKey();
    const saved = JSON.parse(localStorage.getItem(key) || '{}');
    saved.avatar = dataUrl;
    localStorage.setItem(key, JSON.stringify(saved));

    const curr = JSON.parse(localStorage.getItem('current_user') || '{}');
    curr.avatar = dataUrl;
    localStorage.setItem('current_user', JSON.stringify(curr));
  } catch (e) { }

  syncUserProfileUI();
}

function removeUserAvatar() {
  try {
    const key = getUserProfileStorageKey();
    const saved = JSON.parse(localStorage.getItem(key) || '{}');
    delete saved.avatar;
    localStorage.setItem(key, JSON.stringify(saved));

    const curr = JSON.parse(localStorage.getItem('current_user') || '{}');
    delete curr.avatar;
    localStorage.setItem('current_user', JSON.stringify(curr));
  } catch (e) { }

  const fileInput = document.getElementById('userAvatarFileInput');
  if (fileInput) fileInput.value = '';

  syncUserProfileUI();
}

function saveUserProfileDetails() {
  const name = document.getElementById('userProfInputName').value.trim();
  const email = document.getElementById('userProfInputEmail').value.trim();
  const dept = document.getElementById('userProfInputDept').value.trim();

  if (!name || !email) {
    alert('Please fill in all required fields.');
    return;
  }

  // Persist to user-scoped localStorage
  try {
    const key = getUserProfileStorageKey();
    const saved = JSON.parse(localStorage.getItem(key) || '{}');
    saved.name = name;
    saved.email = email;
    saved.dept = dept;
    localStorage.setItem(key, JSON.stringify(saved));

    const curr = JSON.parse(localStorage.getItem('current_user') || '{}');
    curr.name = name;
    curr.email = email;
    curr.department = dept;
    localStorage.setItem('current_user', JSON.stringify(curr));
  } catch (e) { }

  syncUserProfileUI();
  toggleUserEditProfileForm(false);
  alert('Profile details have been successfully saved!');
}

function syncUserProfileUI() {
  let currUser = {};
  try {
    currUser = JSON.parse(localStorage.getItem('current_user') || '{}');
  } catch (e) { }

  const key = getUserProfileStorageKey();
  let saved = {};
  try {
    saved = JSON.parse(localStorage.getItem(key) || '{}');
  } catch (e) { }

  const username = currUser.username || 'user';
  const userName = saved.name || currUser.name || currUser.full_name || username;
  const email = saved.email || currUser.email || (username ? username + '@manila.gov.ph' : 'user@manila.gov.ph');
  const dept = saved.dept || currUser.department || 'City Council Secretariat';
  const role = currUser.role ? (currUser.role.charAt(0).toUpperCase() + currUser.role.slice(1)) : 'Councilor';
  const dateProvisioned = currUser.created_at || new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
  const avatar = saved.avatar || currUser.avatar || '';

  // Update Topbar User Dropdown Button Text & Image
  const topbarNameEl = document.getElementById('topbarUserName');
  if (topbarNameEl) {
    topbarNameEl.textContent = userName;
  }

  const topbarAvatarImg = document.getElementById('topbarUserAvatarImg');
  const topbarAvatarFallback = document.getElementById('topbarUserAvatarFallback');
  if (topbarAvatarImg && topbarAvatarFallback) {
    if (avatar) {
      topbarAvatarImg.src = avatar;
      topbarAvatarImg.style.display = 'block';
      topbarAvatarImg.classList.remove('d-none');
      topbarAvatarFallback.style.display = 'none';
      topbarAvatarFallback.classList.add('d-none');
    } else {
      topbarAvatarImg.src = '';
      topbarAvatarImg.style.display = 'none';
      topbarAvatarImg.classList.add('d-none');
      topbarAvatarFallback.style.display = 'flex';
      topbarAvatarFallback.classList.remove('d-none');
    }
  }

  // Update Profile Section Header
  const profHeaderName = document.getElementById('userProfileHeaderName');
  if (profHeaderName) profHeaderName.textContent = userName;

  const profHeaderUsername = document.getElementById('userProfileHeaderUsername');
  if (profHeaderUsername) profHeaderUsername.textContent = username;

  const profHeaderEmail = document.getElementById('userProfileHeaderEmail');
  if (profHeaderEmail) profHeaderEmail.textContent = email;

  const profHeaderRole = document.getElementById('userProfileHeaderRole');
  if (profHeaderRole) profHeaderRole.textContent = role + ' / Member';

  const profInputName = document.getElementById('userProfInputName');
  if (profInputName && (document.getElementById('userProfileEditActions')?.classList.contains('d-none') || !profInputName.value)) {
    profInputName.value = userName;
  }

  const profInputUsername = document.getElementById('userProfInputUsername');
  if (profInputUsername) profInputUsername.value = username;

  const profInputEmail = document.getElementById('userProfInputEmail');
  if (profInputEmail && (document.getElementById('userProfileEditActions')?.classList.contains('d-none') || !profInputEmail.value)) {
    profInputEmail.value = email;
  }

  const profInputDept = document.getElementById('userProfInputDept');
  if (profInputDept && (document.getElementById('userProfileEditActions')?.classList.contains('d-none') || !profInputDept.value)) {
    profInputDept.value = dept;
  }

  const profInputRole = document.getElementById('userProfInputRole');
  if (profInputRole) profInputRole.value = role;

  const profInputDate = document.getElementById('userProfInputDate');
  if (profInputDate) profInputDate.value = dateProvisioned;

  // Update Profile Section Avatar Card
  const profHeaderAvatarImg = document.getElementById('userProfileHeaderAvatarImg');
  const profHeaderAvatarFallback = document.getElementById('userProfileHeaderAvatarFallback');
  const removeAvatarBtn = document.getElementById('removeUserAvatarBtn');
  if (profHeaderAvatarImg && profHeaderAvatarFallback) {
    if (avatar) {
      profHeaderAvatarImg.src = avatar;
      profHeaderAvatarImg.style.display = 'block';
      profHeaderAvatarImg.classList.remove('d-none');
      profHeaderAvatarFallback.style.display = 'none';
      profHeaderAvatarFallback.classList.add('d-none');
      if (removeAvatarBtn) {
        removeAvatarBtn.style.display = 'inline-flex';
        removeAvatarBtn.classList.remove('d-none');
      }
    } else {
      profHeaderAvatarImg.src = '';
      profHeaderAvatarImg.style.display = 'none';
      profHeaderAvatarImg.classList.add('d-none');
      profHeaderAvatarFallback.style.display = 'flex';
      profHeaderAvatarFallback.classList.remove('d-none');
      if (removeAvatarBtn) {
        removeAvatarBtn.style.display = 'none';
        removeAvatarBtn.classList.add('d-none');
      }
    }
  }
}

window.syncUserProfileUI = syncUserProfileUI;
window.handleUserAvatarUpload = handleUserAvatarUpload;
window.saveUserAvatarData = saveUserAvatarData;
window.removeUserAvatar = removeUserAvatar;
window.saveUserProfileDetails = saveUserProfileDetails;
window.toggleUserEditProfileForm = toggleUserEditProfileForm;

// Restore saved profile details on page load
document.addEventListener('DOMContentLoaded', syncUserProfileUI);
if (document.readyState === 'complete' || document.readyState === 'interactive') {
  syncUserProfileUI();
}
</script>
