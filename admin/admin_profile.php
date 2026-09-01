<section id="adminProfileSection"
  class="content-section <?= $active_section !== 'adminProfileSection' ? 'd-none' : '' ?>">

  <!-- Page Header -->
  <div class="mb-4">
    <h2 class="h4 fw-bold text-dark mb-1">Administrator Profile</h2>
    <p class="text-muted small mb-0">Manage your administrator account details, profile picture, and system access.</p>
  </div>

  <!-- Profile Header Card -->
  <div class="card border shadow-sm rounded-4 p-4 mb-4 bg-white">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
      <div class="d-flex align-items-center gap-3">
        <div class="position-relative">
          <div id="profileAvatarContainer"
            class="rounded-circle d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary flex-shrink-0 overflow-hidden border border-2 border-primary border-opacity-25 shadow-sm"
            style="width: 70px; height: 70px;">
            <img id="profileHeaderAvatarImg" src="" alt="Admin Profile" class="w-100 h-100 object-fit-cover d-none" />
            <i id="profileHeaderAvatarFallback" class="bi bi-person-fill" style="font-size: 2.2rem;"></i>
          </div>
          <button type="button" class="btn btn-primary btn-sm rounded-circle position-absolute bottom-0 end-0 p-0 d-flex align-items-center justify-content-center shadow"
            style="width: 26px; height: 26px;" onclick="document.getElementById('adminAvatarFileInput').click()" title="Change Profile Picture">
            <i class="bi bi-camera-fill" style="font-size: 0.75rem;"></i>
          </button>
          <input type="file" id="adminAvatarFileInput" accept="image/*" class="d-none" onchange="handleAdminAvatarUpload(this)">
        </div>
        <div>
          <h3 class="h4 fw-bold text-dark mb-1" id="profileHeaderName">Manila City Hall Administrator</h3>
          <div class="text-secondary fw-medium" style="font-size: 0.92rem;">
            <i class="bi bi-at text-muted me-0.5"></i><span id="profileHeaderUsername">admin</span> &bull;
            <i class="bi bi-envelope text-muted ms-1 me-1"></i><span id="profileHeaderEmail">admin@manila.gov.ph</span>
            &bull;
            <i class="bi bi-building text-muted ms-1 me-1"></i><span id="profileHeaderDept">Office of the Mayor /
              Secretariat</span>
          </div>
        </div>
      </div>

      <!-- Redesigned Modern Action Buttons -->
      <div class="d-flex flex-wrap align-items-center gap-2.5">
        <button type="button" class="btn btn-action-photo rounded-3 px-3.5 py-2 shadow-2xs d-inline-flex align-items-center gap-2"
          onclick="document.getElementById('adminAvatarFileInput').click()">
          <i class="bi bi-camera text-primary fs-6"></i>
          <span>Change Photo</span>
        </button>
        <button type="button" id="removeAdminAvatarBtn" class="btn btn-action-danger rounded-3 px-3.5 py-2 shadow-2xs d-inline-flex align-items-center gap-2 d-none"
          onclick="removeAdminAvatar()" title="Remove Photo">
          <i class="bi bi-trash3 text-danger fs-6"></i>
          <span>Remove Photo</span>
        </button>
        <button type="button" class="btn btn-action-primary rounded-3 px-3.5 py-2 shadow-sm d-inline-flex align-items-center gap-2"
          onclick="toggleEditProfileForm(true)">
          <i class="bi bi-pencil-square fs-6"></i>
          <span>Edit Profile</span>
        </button>
        <button type="button" class="btn btn-action-warning rounded-3 px-3.5 py-2 shadow-2xs d-inline-flex align-items-center gap-2"
          data-bs-toggle="modal" data-bs-target="#changeAdminPasswordModal">
          <i class="bi bi-key-fill fs-6" style="color: #d97706;"></i>
          <span>Change Password</span>
        </button>
      </div>
    </div>
  </div>

  <!-- Card: Account Details (Spacious, Larger Form/Table) -->
  <div class="card border shadow-sm rounded-4 p-4 p-md-5 bg-white mb-4">
    <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-bottom">
      <div class="d-flex align-items-center gap-3">
        <div class="rounded-3 bg-primary bg-opacity-10 text-primary p-2.5 d-flex align-items-center justify-content-center"
          style="width: 44px; height: 44px;">
          <i class="bi bi-person-vcard fs-4"></i>
        </div>
        <div>
          <h3 class="h5 fw-bold text-dark mb-0">Account Details</h3>
          <p class="text-muted small mb-0 mt-0.5" style="font-size: 0.85rem;">Official administrator profile information and account credentials</p>
        </div>
      </div>
      <div id="profileEditActions" class="d-none">
        <button type="button" class="btn btn-light border px-3.5 py-2 rounded-3 me-2 fw-semibold"
          onclick="toggleEditProfileForm(false)">Cancel</button>
        <button type="button" class="btn btn-primary fw-semibold px-4 py-2 rounded-3 shadow-sm" onclick="saveAdminProfileDetails()">
          <i class="bi bi-check-lg me-1"></i> Save Changes
        </button>
      </div>
    </div>

    <form id="adminProfileForm" onsubmit="saveAdminProfileDetails(); return false;">
      <div class="row g-4">
        <div class="col-12 col-md-6">
          <label class="form-label fw-semibold text-dark mb-2" style="font-size: 0.95rem;">Account Holder Name</label>
          <input type="text" id="profInputName" class="form-control form-control-lg bg-light py-2.5 px-3 fs-6 rounded-3"
            value="Manila City Hall Administrator" readonly required style="font-size: 0.98rem;">
        </div>

        <div class="col-12 col-md-6">
          <label class="form-label fw-semibold text-dark mb-2" style="font-size: 0.95rem;">Username</label>
          <input type="text" id="profInputUsername" class="form-control form-control-lg bg-light py-2.5 px-3 fs-6 rounded-3" value="admin" readonly
            disabled style="font-size: 0.98rem;">
        </div>

        <div class="col-12 col-md-6">
          <label class="form-label fw-semibold text-dark mb-2" style="font-size: 0.95rem;">Official Email Address</label>
          <input type="email" id="profInputEmail" class="form-control form-control-lg bg-light py-2.5 px-3 fs-6 rounded-3"
            value="admin@manila.gov.ph" readonly required style="font-size: 0.98rem;">
        </div>

        <div class="col-12 col-md-6">
          <label class="form-label fw-semibold text-dark mb-2" style="font-size: 0.95rem;">Assigned Department</label>
          <input type="text" id="profInputDept" class="form-control form-control-lg bg-light py-2.5 px-3 fs-6 rounded-3"
            value="Office of the Mayor / Secretariat" readonly required style="font-size: 0.98rem;">
        </div>

        <div class="col-12 col-md-12">
          <label class="form-label fw-semibold text-dark mb-2" style="font-size: 0.95rem;">Date Provisioned</label>
          <input type="text" id="profInputDate" class="form-control form-control-lg bg-light py-2.5 px-3 fs-6 rounded-3" value="August 10, 2026"
            readonly disabled style="font-size: 0.98rem;">
        </div>
      </div>
    </form>
  </div>

</section>

<script>
  function toggleEditProfileForm(isEditing) {
    const editableIds = ['profInputName', 'profInputEmail', 'profInputDept'];
    const actions = document.getElementById('profileEditActions');

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

    if (isEditing && document.getElementById('profInputName')) {
      document.getElementById('profInputName').focus();
    }
  }

  function handleAdminAvatarUpload(input) {
    if (input.files && input.files[0]) {
      const file = input.files[0];
      if (file.size > 5 * 1024 * 1024) {
        alert('Image file is too large. Please select an image under 5MB.');
        return;
      }
      const reader = new FileReader();
      reader.onload = function (e) {
        const dataUrl = e.target.result;
        saveAdminAvatarData(dataUrl);
      };
      reader.readAsDataURL(file);
    }
  }

  function saveAdminAvatarData(dataUrl) {
    try {
      const saved = JSON.parse(localStorage.getItem('admin_profile_data') || '{}');
      saved.avatar = dataUrl;
      localStorage.setItem('admin_profile_data', JSON.stringify(saved));

      const curr = JSON.parse(localStorage.getItem('current_user') || '{}');
      curr.avatar = dataUrl;
      localStorage.setItem('current_user', JSON.stringify(curr));
    } catch (e) { }

    syncAdminProfileUI();
  }

  function removeAdminAvatar() {
    try {
      const saved = JSON.parse(localStorage.getItem('admin_profile_data') || '{}');
      delete saved.avatar;
      localStorage.setItem('admin_profile_data', JSON.stringify(saved));

      const curr = JSON.parse(localStorage.getItem('current_user') || '{}');
      delete curr.avatar;
      localStorage.setItem('current_user', JSON.stringify(curr));
    } catch (e) { }

    const fileInput = document.getElementById('adminAvatarFileInput');
    if (fileInput) fileInput.value = '';

    syncAdminProfileUI();
  }

  function saveAdminProfileDetails() {
    const name = document.getElementById('profInputName').value.trim();
    const email = document.getElementById('profInputEmail').value.trim();
    const dept = document.getElementById('profInputDept').value.trim();

    if (!name || !email) {
      alert('Please fill in all required fields.');
      return;
    }

    // Persist to localStorage
    try {
      const saved = JSON.parse(localStorage.getItem('admin_profile_data') || '{}');
      saved.name = name;
      saved.email = email;
      saved.dept = dept;
      localStorage.setItem('admin_profile_data', JSON.stringify(saved));

      const curr = JSON.parse(localStorage.getItem('current_user') || '{}');
      curr.name = name;
      curr.email = email;
      curr.department = dept;
      localStorage.setItem('current_user', JSON.stringify(curr));
    } catch (e) { }

    syncAdminProfileUI();
    toggleEditProfileForm(false);
    alert('Administrator profile details have been successfully saved!');
  }

  function syncAdminProfileUI() {
    let adminName = 'Manila City Hall Administrator';
    let email = 'admin@manila.gov.ph';
    let dept = 'Office of the Mayor / Secretariat';
    let avatar = '';

    try {
      const saved = JSON.parse(localStorage.getItem('admin_profile_data') || '{}');
      const curr = JSON.parse(localStorage.getItem('current_user') || '{}');

      if (saved.name) adminName = saved.name;
      else if (curr.name && curr.name !== 'Admin' && curr.name !== 'admin') adminName = curr.name;

      if (saved.email) email = saved.email;
      else if (curr.email) email = curr.email;

      if (saved.dept) dept = saved.dept;
      else if (curr.department) dept = curr.department;

      if (saved.avatar) avatar = saved.avatar;
      else if (curr.avatar) avatar = curr.avatar;
    } catch (e) { }

    // Update Topbar Admin Dropdown Button Text & Image
    const topbarNameEl = document.getElementById('topbarAdminName');
    if (topbarNameEl) {
      topbarNameEl.textContent = adminName;
    }

    const topbarAvatarImg = document.getElementById('topbarAdminAvatarImg');
    const topbarAvatarFallback = document.getElementById('topbarAdminAvatarFallback');
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
    const profHeaderName = document.getElementById('profileHeaderName');
    if (profHeaderName) profHeaderName.textContent = adminName;

    const profHeaderEmail = document.getElementById('profileHeaderEmail');
    if (profHeaderEmail) profHeaderEmail.textContent = email;

    const profHeaderDept = document.getElementById('profileHeaderDept');
    if (profHeaderDept) profHeaderDept.textContent = dept;

    const profInputName = document.getElementById('profInputName');
    if (profInputName && (document.getElementById('profileEditActions')?.classList.contains('d-none') || !profInputName.value)) {
      profInputName.value = adminName;
    }

    const profInputEmail = document.getElementById('profInputEmail');
    if (profInputEmail && (document.getElementById('profileEditActions')?.classList.contains('d-none') || !profInputEmail.value)) {
      profInputEmail.value = email;
    }

    const profInputDept = document.getElementById('profInputDept');
    if (profInputDept && (document.getElementById('profileEditActions')?.classList.contains('d-none') || !profInputDept.value)) {
      profInputDept.value = dept;
    }

    // Update Profile Section Avatar Card
    const profHeaderAvatarImg = document.getElementById('profileHeaderAvatarImg');
    const profHeaderAvatarFallback = document.getElementById('profileHeaderAvatarFallback');
    const removeAvatarBtn = document.getElementById('removeAdminAvatarBtn');
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
        profHeaderAvatarFallback.style.display = 'block';
        profHeaderAvatarFallback.classList.remove('d-none');
        if (removeAvatarBtn) {
          removeAvatarBtn.style.display = 'none';
          removeAvatarBtn.classList.add('d-none');
        }
      }
    }
  }

  window.syncAdminProfileUI = syncAdminProfileUI;
  window.handleAdminAvatarUpload = handleAdminAvatarUpload;
  window.saveAdminAvatarData = saveAdminAvatarData;
  window.removeAdminAvatar = removeAdminAvatar;
  window.saveAdminProfileDetails = saveAdminProfileDetails;
  window.toggleEditProfileForm = toggleEditProfileForm;

  // Restore saved profile details on page load
  document.addEventListener('DOMContentLoaded', syncAdminProfileUI);
  if (document.readyState === 'complete' || document.readyState === 'interactive') {
    syncAdminProfileUI();
  }
</script>