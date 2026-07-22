<main class="login-page">

    <div class="login-card">

        <div class="login-logo">Gloop</div>

        <h1>Create Account</h1>

        <p class="login-subtitle">
            Sign up to rate movies, create lists and share with friends.
        </p>

        <?php if ($error): ?>
            <p class="login-error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form class="login-form" action="/signup" method="POST">

            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" placeholder="Choose a username" required>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="you@example.com" required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Create a password" required>
            </div>

            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" placeholder="Repeat your password" required>
            </div>

            <button type="submit" class="login-submit">
                Sign Up
            </button>

        </form>

        <p class="login-footer">
            Already have an account?
            <a href="/login">Log in</a>
        </p>

    </div>

</main>