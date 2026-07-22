<main class="login-page">

    <div class="login-card">

        <div class="login-logo">
            Gloop
        </div>

        <h1>Sign In</h1>

        <p class="login-subtitle">
            Log in to rate movies and share lists with friends.
        </p>

        <?php if ($error): ?>
            <p class="login-error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form class="login-form" action="/login" method="POST">

            <div class="form-group">
                <label>Email</label>

                <input 
                    type="email"
                    id="email" 
                    name="email"
                    placeholder="you@example.com"
                    required
                >
            </div>

            <div class="form-group">
                <label>Password</label>

                <input 
                    type="password"
                    id="password" 
                    name="password"
                    placeholder="Enter your password"
                    required
                >
            </div>

            <button type="submit" class="login-submit">
                Log In
            </button>

        </form>

        <p class="login-footer">
            Don’t have an account?
            <a href="/signup">Sign up</a>
        </p>

    </div>

</main>