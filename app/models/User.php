<?php
require_once(realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '../../includes/Database.php'));

class User {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    //Find user by email
    public function findUserByEmail($email){
        // Prepare and execute statement
        $this->db->query('SELECT * FROM users WHERE email = :email');
        $this->db->bind(':email', $email);
        $row = $this->db->fetch();
        //Check row
        if($this->db->rowCount() > 0){
            return $row;
        }else{
            return false;
        }
    }
    
    //Register User
    public function register($userData){
        // Perform registration logic using $userData array
        $email = $userData['email'];
        $password = $userData['password'];
        $firstName = $userData['first_name'];
        $lastName = $userData['last_name'];
        $token = $userData['token'];
        $token_expiration = $userData['token_expiration'];
        $this->db->query("INSERT INTO users (email, password, first_name, last_name, token, token_expiration) VALUES (?, ?, ?, ?, ?, ?)");
        // ... bind parameters ...
        $this->db->bind(1, $email);
        $this->db->bind(2, $password);
        $this->db->bind(3, $firstName);
        $this->db->bind(4, $lastName);
        $this->db->bind(5, $token);
        $this->db->bind(6, $token_expiration);
        // ... execute statement ...
        if($this->db->execute()){
            return true;
        }else{
            return false;
        }
    }

    //Validate account
    public function validate($user){
        // Perform registration logic using $userData array
        $token = bin2hex(random_bytes(32));
        $token_expiration = date('Y-m-d H:i:s');
        $this->db->query("UPDATE users SET isValid = 1, token = ?, token_expiration = ?");
        $this->db->bind(1, $token);
        $this->db->bind(2, $token_expiration);
        // ... execute statement ...
        if($this->db->execute()){
            return true;
        }else{
            return false;
        }
        if($this->db->execute()){
            return true;
        }else{
            return false;
        }
    }
    
     //Login user
     public function login($email, $password){
        $row = $this->findUserByEmail($email);

        if($row == false) return false;

        $hashedPassword = $row->password;
        if(password_verify($password, $hashedPassword)){
            //Reset account lockout
            $this->db->query('UPDATE users SET failed_login_attempts = 0, locked = 0, lockout_expiration = CURRENT_TIMESTAMP WHERE email = :email');
            $this->db->bind(':email', $email);
            $this->db->execute();
            //Return Logged user
            return $row;
        }else{
            return false;
        }
    }

    //Reset Password
    public function resetPassword($email, $token, $token_expiration){
        $this->db->query('UPDATE users SET token=:token, token_expiration=:token_expiration WHERE email=:email');
        $this->db->bind(':token', $token);
        $this->db->bind(':token_expiration', $token_expiration);
        $this->db->bind(':email', $email);

        if($this->db->execute()){
            return true;
        }else{
            return false;
        }
    }

    //New Password
    public function newPassword($newPassword, $token){
        $this->db->query('UPDATE users SET password=:newPassword WHERE token=:token');
        $this->db->bind(':newPassword', $newPassword);
        $this->db->bind(':token', $token);

        if($this->db->execute()){
            return true;
        }else{
            return false;
        }
    }

    //Find user by token
    public function getUserByToken($token){
        $this->db->query('SELECT * FROM users WHERE token = :token');
        $this->db->bind(':token', $token);
        $row = $this->db->fetch();
        //Check row
        if($this->db->rowCount() > 0){
            return $row;
        }else{
            return false;
        }
    }

    //Get failed login attempts count
    public function getFailedLoginAttempts($email){
        $this->db->query('SELECT * FROM users WHERE email = :email');
        $this->db->bind(':email', $email);
        $row = $this->db->fetch();
        //Check row
        if($this->db->rowCount() > 0){
            return $row->failed_login_attempts;
        }else{
            return false;
        }
    }

    //Increment failed login attempts count
    public function incrementFailedLoginAttempts($email){
        $this->db->query('UPDATE users SET failed_login_attempts = failed_login_attempts + 1 WHERE email = :email');
        $this->db->bind(':email', $email);
        $this->db->execute();
        //Check row
        if($this->db->rowCount() > 0){
            return true;
        }else{
            return false;
        }
    }

    //Lock user account
    public function lockUserAccount($email, $lockoutExpiration) {
        $this->db->query('UPDATE users SET locked = 1, lockout_expiration = :lockout_expiration WHERE email = :email');
        $this->db->bind(':lockout_expiration', $lockoutExpiration);
        $this->db->bind(':email', $email);
        $this->db->execute();
        //Check row
        if ($this->db->rowCount() > 0) {
            return true;
        } else {
            return false;
        }
    }

}

