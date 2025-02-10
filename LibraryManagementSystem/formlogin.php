

<div class="form-box login">
    <h2>Login</h2>
    <form method="POST">
        <input type="hidden" name="action" value="login">
        <div class="input-box">
            <span class="icon"><ion-icon name="mail"></ion-icon></span>
            <input type="email" name="email" required placeholder="">
            <label>Email</label>
        </div>
        <div class="input-box">
            <span class="icon"><ion-icon name="lock-closed"></ion-icon></span>
            <input type="password" name="password" required placeholder="">
            <label>Password</label>
        </div>
        <?php
        if (isset($_SESSION['success_message'])) {
            echo '<script type="text/javascript">
                    window.onload = function() {
                        alert("' . $_SESSION['success_message'] . '");
                    };
                  </script>';
            unset($_SESSION['success_message']); // Unset after displaying
        }
        ?>
        <div class="remember-forgot">
            <label><input type="checkbox"> Remember me</label>
            <a href="#">Forget Password?</a>
        </div>
        <button type="submit" class="btn-log">Login</button>
        <div class="login-register">
            <p>Dont Have an Account? <a href="#" class="register-link">Sign Up</a></p>
        </div>
    </form>
</div>