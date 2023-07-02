<?php
//Time Zone
date_default_timezone_set('Africa/Tunis');

//Error handling
ini_set('display_errors', 1);
error_reporting(E_ALL);

//Session handling
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => true,  
    'httponly' => true,
    'samesite' => 'Strict' 
]);
session_start();

//Load User Model
require_once __DIR__ . '\..\models\User.php';

//Load Composer's autoloader
require_once __DIR__ . '\..\..\vendor\autoload.php';

//Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;


class Users {

    private $userModel;
    
    public function __construct(){
        $this->generateCSRFToken();
        $this->userModel = new User;
    }

    // Generate CSRF Token
    private function generateCSRFToken(){
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }    

    public function register(){
        // Sanitize inputs
        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    
        // Init data
        $userData = [
            'first_name' => $_POST['first_name'],
            'last_name' => $_POST['last_name'],
            'email' => $_POST['email'],
            'password' => $_POST['password'],
            'passwordRepeat' => $_POST['passwordRepeat']
        ];        
    
        // Validate inputs
        $errors = [];
    
        if(empty($userData['first_name']) || empty($userData['last_name']) || empty($userData['email']) || empty($userData['password'])){
            $errors[] = "Please fill out all inputs";
        }
    
        if(!preg_match("/^[a-zA-Z\s'-]+$/", $userData['first_name'])){
            $errors[] = "Invalid firstname";
        }
    
        if(!preg_match("/^[a-zA-Z\s'-]+$/", $userData['last_name'])){
            $errors[] = "Invalid lastname";
        }
    
        if(!filter_var($userData['email'], FILTER_VALIDATE_EMAIL)){
            $errors[] = "Invalid email";
        }
    
        if( (strlen($userData['password']) < 8 ) || ( strlen($userData['password']) > 24) ){
            $errors[] = "Password must be between 8 and 24 characters long";
        }
        else if(!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z\d]).+$/", $userData['password'])){
            $errors[] = "Password must contain at least one lowercase letter, one uppercase letter, one numeric digit, and one special character";
        }
        else if($userData['password'] !== $userData['passwordRepeat']){
            $errors[] = "Passwords don't match";
        } 
    
        // Check for validation errors
        if(!empty($errors)){
            // Store errors in session
            $_SESSION['status'] = implode('\n', $errors);
            header("location: /register");
            exit();
        }
    
        // User with the same email already exists
        if($this->userModel->findUserByEmail($userData['email'])){
            $_SESSION['status'] = "Email already taken";
            header("location: /register");
            exit();
        }
    
                                // Passed all validation checks

        // Hash password
        $userData['password'] = password_hash($userData['password'], PASSWORD_DEFAULT);

        // Generate verification token
        $userData['token'] = bin2hex(random_bytes(32));
        $validiyDuration = 3600;
        $token_expiration = time() + $validiyDuration;
        $token_expiration = date('Y-m-d H:i:s', $token_expiration);
        $userData['token_expiration'] = $token_expiration;
    
        // Register User
        if($this->userModel->register($userData)){
            // Send Validation and provide feedback to the user
            $validateLink = "http://localhost:9000/validate?token=" . $userData['token'];
            $subject = "Account Validation";
            $message = "Click the following link to validate your account: " . $validateLink;
            $this->sendMail($userData['email'], $subject, $message);
            var_dump($this->sendMail($userData['email'], $subject, $message));
            
        } else {
            // Handle database or other errors
            $_SESSION['status'] = "Something went wrong";
            header("location: /register");
            exit();
        }
    }    

    public function login(){
        //Sanitize inputs
        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        //Init data
        $userData=[
            'email' => $_POST['email'],
            'password' => $_POST['password']
        ];

        // Validate inputs
        $errors = [];


        if(empty($userData['email']) || empty($userData['password'])){
            $errors[] = "Please fill out all inputs";
        }

        if(!filter_var($userData['email'], FILTER_VALIDATE_EMAIL)){
            //Make message generic
            $errors[] = "Invalid email or password";
        }
    
        // Check for validation errors
        if(!empty($errors)){
            // Store errors in session
            $_SESSION['status'] = implode('\n', $errors);
            header("location: /login");
            exit();
        }

        //Check if user exists and login
        if($this->userModel->findUserByEmail($userData['email'])){
            $loggedInUser = $this->userModel->login($userData['email'], $userData['password']);
            $locked = $this->userModel->findUserByEmail($userData['email'])->locked;
            $lockout_expiration = $this->userModel->findUserByEmail($userData['email'])->lockout_expiration;
            $currentDateTime = date('Y-m-d H:i:s');
            // Check if the account is valid
            if ($loggedInUser->isValid == 0){
                // Display an error message to the user
                $_SESSION['status'] = "Your account is not valid yet. Please check your email.";
                header("location: /login");
                exit();
            }
            // Check if the account is locked
            if ($locked && $lockout_expiration > $currentDateTime){
                // Display an error message to the user
                $_SESSION['status'] = "Your account is locked. Please try again later.";
                header("location: /login");
                exit();
            }
            //User Found with correct credantials
            if($loggedInUser){
                // Regenerate session ID
                session_regenerate_id(true);
                //Set session details
                $this->createUserSession($loggedInUser);
            //User Found with incorrect credantials
            }else{
                $this->handleFailedLogin($userData['email']);
            }
        //User Not Found
        }else{
            $this->handleFailedLogin($userData['email']);
        }
    }

    private function createUserSession($loggedInUser){
        $_SESSION['full_name'] = $loggedInUser->first_name . ' ' . $loggedInUser->last_name;
        header("location: /");
        exit();
    }

    private function handleFailedLogin($email){
        // Increment the failed login attempts for the user
        try{
            $this->userModel->incrementFailedLoginAttempts($email);
        }catch(Exception $e){
            echo 'incrementFailedLoginAttempts Failed :'.' '.$e->getMessage();
            exit;
        }
    
        // Get the current failed login attempts for the user
        try{
            $failedAttempts = $this->userModel->getFailedLoginAttempts($email);
        }catch(Exception $e){
            echo 'incrementFailedLoginAttempts Failed :'.' '.$e->getMessage();
            exit;
        }
        
        // Lock the user account if the maximum failed attempts have been reached
        $maxAttempts = 5;
        $lockoutDuration = 300; // 5 minutes
    
        if($failedAttempts >= $maxAttempts){
            $lockoutExpiration = time() + $lockoutDuration;
            $lockoutExpiration = date('Y-m-d H:i:s', $lockoutExpiration);
            if (!$this->userModel->lockUserAccount($email, $lockoutExpiration)) {
                // Handle the error if locking the account fails
                echo 'lockUserAccount Failed';
                exit;
            }
            // Display a specific error message to the user
            $_SESSION['status'] = "Your account has been locked due to multiple failed login attempts. Please try again later.";
            header("location: /login");
            exit();
        }
    
        // Display an error message to the user
        $_SESSION['status'] = "Invalid email or password";
        header("location: /login");
        exit();
    }

    public function logout(){
        unset($_SESSION['full-name']);
        session_destroy();
        header("location: /login");
        exit();
    }

    public function resetPassword(){
        //Sanitize inputs
        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        // Get the submitted email address
        $email = $_POST['email'];
        // Validate inputs
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['status'] = "Invalid email";
            header("location: /resetPassword");
            exit();
        } else {
            // Generate a unique token
            $token = bin2hex(random_bytes(32));
            $validiyDuration = 3600;
            $token_expiration = time() + $validiyDuration;
            $token_expiration = date('Y-m-d H:i:s', $token_expiration);
            //Check for email
            if($this->userModel->findUserByEmail($email)){
                //User Found
                $user = $this->userModel->resetPassword($email, $token, $token_expiration);
                if($user){
                    $resetLink = "http://localhost:9000/newPassword?token=" . $token;
                    $subject = "Password Reset";
                    $message = "Click the following link to reset your password: " . $resetLink;
                    $this->sendMail($email, $subject, $message);
                    $_SESSION['status'] = "Check your email";
                    header("location: /login");
                    exit();
                }else{
                    $_SESSION['status'] = "Could not insert token";
                    header("location: /resetPassword");
                    exit();
                }
            }else{
                $_SESSION['status'] = "No user found";
                header("location: /resetPassword");
                exit();
            }
        }
    }

    public function sendMail($email, $subject, $message){
        //Create an instance; passing `true` enables exceptions
        $mail = new PHPMailer(true);
        // Read SMTP credentials from secrets.txt

        // Get the current directory of the script
        $baseDir = __DIR__;

        // Construct the relative path to the secret file
        $relativePath = '../controllers/secret.txt';

        // Combine the base directory and relative path to get the full relative file path
        $filePath = $baseDir . '/' . $relativePath;

        // Read the contents of the file
        $secrets = file_get_contents($filePath);

        // Split the contents into an array of lines
        $lines = explode("\n", $secrets);

        // Extract the SMTP username and password from the lines
        $smtpUsername = isset($lines[0]) ? trim($lines[0]) : '';
        $smtpPassword = isset($lines[1]) ? trim($lines[1]) : '';
        try {
            //Server settings
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
            $mail->isSMTP();                                            //Send using SMTP
            $mail->Host       = 'smtp.gmail.com';                     //Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
            $mail->Username   = $smtpUsername;                     //SMTP username
            $mail->Password   = $smtpPassword;                               //SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
            $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

            //Recipients
            $mail->setFrom('auth@test.com', 'Authentication System');
            $mail->addAddress($email);     //Add a recipient

            //Content
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = $subject;
            $mail->Body    = $message;
            $mail->AltBody =  $message;

            $mail->send();

        } catch (Exception $e) {
            echo "Message could not be sent : {$mail->ErrorInfo}";
        }
    }

    public function newPassword() {
        if (isset($_POST['token'])) {
            //Sanitize inputs
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            // Get the submitted token
            $token = $_POST['token'];
        
            // Retrieve the user's record based on the token
            $user = $this->userModel->getUserByToken($token);
            
            // Validate the token against the user's record in the database
            $currentDateTime = date('Y-m-d H:i:s');
            $token_expiration = $user->token_expiration;

            if ($user && $token_expiration > $currentDateTime) {
                // Get the submitted password
                $newPassword = $_POST['password'];
                $newPasswordRepeat = $_POST['passwordRepeat'];
                if($newPassword !== $newPasswordRepeat ){
                    $_SESSION['status'] = "Passwords don't match";
                    header('Location:'.'/newPassword'.'?'.'token='.$token);
                    exit();
                }
                $newPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                // Update the user's record in the database with the new password
                $this->userModel->newPassword($newPassword, $token);
                $_SESSION['status'] = "Your password has been reset successfully";
                header("location: /login");
                exit();
            } else {
                // Token is invalid or expired
                echo "Invalid or expired password reset token";
            }
        } else {
            // Token is not provided
            echo "Token not found";
        }
    }

    public function validate() {
        if (isset($_POST['token'])) {
            //Sanitize inputs
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            // Get the submitted token
            $token = $_POST['token'];
        
            // Retrieve the user's record based on the token
            $user = $this->userModel->getUserByToken($token);
            
            // Validate the token against the user's record in the database
            $currentDateTime = date('Y-m-d H:i:s');
            $token_expiration = $user->token_expiration;

            if ($user && $token_expiration > $currentDateTime) {
                $this->userModel->validate($user);
                $_SESSION['status'] = "Your account has been validated successfully";
                header("location: /login");
                exit();
            } else {
                // Token is invalid or expired
                echo "Invalid or expired password reset token";
            }
        } else {
            // Token is not provided
            echo "Token not found";
        }
    }

}

// Requests Handler
$init = new Users;
//Ensure that user is sending a post request
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    //Ensure that user has a CSRF token
    if (isset($_POST['csrf_token']) && $_POST['csrf_token'] == $_SESSION['csrf_token']){
        if ($_POST['submit'] == 'register') $init->register();
        else if ($_POST['submit'] == 'validate') $init->validate();
        else if ($_POST['submit'] == 'login') $init->login();
        else if ($_POST['submit'] == 'resetPassword') $init->resetPassword();
        else if ($_POST['submit'] == 'newPasswordCall') $init->newPassword();
        else if ($_POST['submit'] == 'logout') $init->logout();
        else{
            header("location: /");
            exit();
        }
    }else{
        echo "CSRF Token not found";
    }
}