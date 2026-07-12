const HOME_PAGE = 'acceuil.php';
const SIGNUP_PAGE = 'inscription.php';

const signupForm = document.getElementById('signupForm');
const loginForm = document.getElementById('loginForm');
const toggleBtns = document.querySelectorAll('.toggle-btn');
const messageBox = document.getElementById('authMessage') || createMessageBox();

function createMessageBox() {
    const box = document.createElement('div');
    box.id = 'authMessage';
    box.className = 'auth-message';

    const target = document.querySelector('.signup') || document.body;
    target.prepend(box);

    return box;
}

function showMessage(message, type = 'error') {
    messageBox.textContent = message;
    messageBox.className = `auth-message ${type}`;
}

function clearMessage() {
    messageBox.textContent = '';
    messageBox.className = 'auth-message';
}


function normalizeEmail(email) {
    return email.trim().toLowerCase();
}

function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}


function getFieldValue(id) {
    const field = document.getElementById(id);
    return field ? field.value.trim() : '';
}

function markInvalid(id, invalid) {
    const field = document.getElementById(id);
    if (field) {
        field.classList.toggle('field-error', invalid);
    }
}

function validateSignup(name, email, password) {
    const errors = [];

    markInvalid('name', name.length < 2);
    markInvalid('email', !isValidEmail(email));
    markInvalid('password', password.length < 6);

    if (name.length < 2) {
        errors.push('le nom complet');
    }

    if (!isValidEmail(email)) {
        errors.push('un email valide');
    }

    if (password.length < 6) {
        errors.push('un mot de passe de 6 caractères minimum');
    }

    return errors;
}

function validateLogin(email, password) {
    const errors = [];

    markInvalid('loginEmail', !isValidEmail(email));
    markInvalid('loginPassword', password.length === 0);

    if (!isValidEmail(email)) {
        errors.push('un email valide');
    }

    if (!password) {
        errors.push('votre mot de passe');
    }

    return errors;
}

function switchMode(mode) {
    if (!signupForm || !loginForm) {
        return;
    }

    clearMessage();
    const showSignup = mode === 'signup';

    signupForm.classList.toggle('active', showSignup);
    loginForm.classList.toggle('active', !showSignup);

    toggleBtns.forEach((btn) => {
        btn.classList.toggle('active', btn.dataset.mode === mode);
    });
}

function redirectToSignup(email = '') {
    const query = email ? `?mode=signup&email=${encodeURIComponent(email)}` : '?mode=signup';
    window.location.href = `${SIGNUP_PAGE}${query}`;
}

function initFromUrl() {
    const params = new URLSearchParams(window.location.search);
    const mode = params.get('mode');
    const email = params.get('email');

    if (mode === 'login' || mode === 'signup') {
        switchMode(mode);
    }

    if (email) {
        const signupEmail = document.getElementById('email');
        const loginEmail = document.getElementById('loginEmail');

        if (signupEmail) {
            signupEmail.value = email;
        }

        if (loginEmail) {
            loginEmail.value = email;
        }
    }
}

initFromUrl();
