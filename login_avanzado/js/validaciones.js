document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            let valid = true;
            let message = '';

            if (!email) {
                valid = false;
                message += 'El correo electrónico es requerido.\n';
            } else if (!/\S+@\S+\.\S+/.test(email)) {
                valid = false;
                message += 'El correo electrónico no es válido.\n';
            }

            if (!password) {
                valid = false;
                message += 'La contraseña es requerida.\n';
            } else if (password.length < 6) {
                valid = false;
                message += 'La contraseña debe tener al menos 6 caracteres.\n';
            }

            if (!valid) {
                alert(message);
                e.preventDefault();
            }
        });
    }
});