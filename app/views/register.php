<?php require_once __DIR__ . '/../../includes/header.php'; ?>

    
    <!-- Register form -->
    <div class="login-form">
        <div class="text">
            REGISTER
        </div>
        <!-- Register errors -->
        <div class="errors">
            <?php echo isset($_SESSION['status']) ? htmlspecialchars($_SESSION['status']) : ''; unset($_SESSION['status']);?>
        </div>
        <form action="/Users" method="post">
            <input hidden name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>"><br><br>
            <div class="field">
                <div class="fas fa-user"></div>
                <input type="text" name="first_name" placeholder="First Name" required><br><br>
            </div>
            <div class="field">
                <div class="fas fa-user"></div>
                <input type="text" name="last_name" placeholder="Last Name" required><br><br>
            </div>
            <div class="field">
                <div class="fas fa-envelope"></div>
                <input type="email" name="email" placeholder="example@domain.name" required><br><br>
            </div>
            <div class="field">
                <div class="fas fa-lock"></div>
                <input type="password" name="password" placeholder="Password" required><br><br>
            </div>
            <div class="field">
                <div class="fas fa-redo"></div>
                <input type="password" name="passwordRepeat" placeholder="Repeat Password" required><br><br>
            </div>
            <button type="submit" name="submit" value="register">Register</button>
        </form>
        <div class="link">  
            <p>Already a member ?</p>Sign-In <a href="/login">Here</a>
        </div>
    </div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>