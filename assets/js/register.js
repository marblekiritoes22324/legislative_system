document.getElementById('registerForm').addEventListener('submit', function (e) {
    e.preventDefault();

    const fullName = document.getElementById('fullName').value.trim();
    const email = document.getElementById('email').value.trim();
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirmPassword').value;

    const errorAlert = document.getElementById('errorAlert');
    const errorMessage = document.getElementById('errorMessage');

    // Simple password matching check
    if (password !== confirmPassword) {
        errorMessage.innerText = "Passwords do not match.";
        errorAlert.style.display = 'flex';
        return;
    }

    // Check password complexity
    if (password.length < 6) {
        errorMessage.innerText = "Password must be at least 6 characters long.";
        errorAlert.style.display = 'flex';
        return;
    }

    // Get existing users
    const users = JSON.parse(localStorage.getItem('legislative_system_users') || '[]');

    // Check if username is taken (case insensitive)
    const usernameExists = users.some(u => u.username.toLowerCase() === username.toLowerCase());
    if (usernameExists) {
        errorMessage.innerText = "Username is already taken. Please choose another.";
        errorAlert.style.display = 'flex';
        return;
    }

    // Create new user object
    const newUser = {
        username: username,
        name: fullName,
        position: 'Legislative Staff',
        department: 'City Council Member',
        email: email,
        password: password,
        status: 'approved'
    };

    // Add to users and save
    users.push(newUser);
    localStorage.setItem('legislative_system_users', JSON.stringify(users));

    // Registration successful — redirect to sign in
    alert('Account created successfully! You can now sign in.');
    window.location.href = 'login.php';
});
