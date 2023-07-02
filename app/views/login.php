<?php require_once __DIR__ . '/../../includes/header.php'; ?>

    

    <!-- Login form -->
    <div class="login-form">
        <div class="text">
            LOGIN
        </div>
        <!-- Login errors -->
        <div class="errors">
            <?php echo isset($_SESSION['status']) ? htmlspecialchars($_SESSION['status']) : ''; unset($_SESSION['status']);?>
        </div>
        <form action="/Users" method="post">
            <input hidden name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>"><br><br>
            <div class="field">
               <div class="fas fa-envelope"></div>
                <input type="email" name="email" placeholder="example@domain.name" required><br><br>
            </div>
            <div class="field">
               <div class="fas fa-lock"></div>
                <input type="password" name="password"  placeholder="Password" required><br><br>
            </div>
            <button type="submit" name="submit" value="login">Login</button>
        </form>
        <div class="link">
            <p>Forgot your password ? Click <a href="/resetPassword">Here</a></p>
        </div> 
        <div class="link">    
            <p>Not a member ? Register <a href="/register">Here</a></p>
        </div>
    </div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>