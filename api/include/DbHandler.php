<?php
/**
 * Class to handle all db operations
 * This class will have CRUD methods for database tables
 * Author: Generaleye
 */
class DbHandler
{

    private $conn;

    function __construct()
    {
        require_once dirname(__FILE__) . '/DbConnect.php';
        // opening db connection
        $db = new DbConnect();
        $this->conn = $db->connect();
    }


    /* ------------- `users` table method ------------------ */

    /**
     * Creating new user
     * @param $first_name
     * @param $last_name
     * @param $email
     * @param $password
     * @param $phone
     * @return int
     */
    public function createUser($first_name, $last_name, $email, $password) {
        require_once 'PassHash.php';
        require_once '../libs/sendgrid-php/sendgrid-php.php';
        require_once 'SendGridEmail.php';

        // Check if user already exists in db
        if (!$this->isEmailExists($email)) {
            // Generating password hash
            $password_hash = PassHash::hash($password);

            // Generating API key
            $token = $this->generateToken();

            if (!$this->isTokenExists($token)) {

                //Get current datetime
                $datetime = date("Y-m-d H:i:s", time());
                //Set active status to 2 = unverified
                $active_state = 2;

                $verifyToken = rand(1,9).rand(1,9).rand(1,9).rand(1,9).rand(1,9).rand(1,9);

                // insert query
                $sql = "INSERT INTO users (`first_name`,`last_name`,`email_address`,`password`,`token`, `verification_token`, `created_time`, `modified_time`, `active_status`) VALUES
                        (:fname, :lname, :email, :password, :token, :verification_token, :created_time, :modified_time, :active_state)";
                try {
                    $stmt = $this->conn->prepare($sql);
                    $stmt->bindParam("fname", $first_name);
                    $stmt->bindParam("lname", $last_name);
                    $stmt->bindParam("email", $email);
                    $stmt->bindParam("password", $password_hash);
                    $stmt->bindParam("token", $token);
                    $stmt->bindParam("verification_token", $verifyToken);
                    $stmt->bindParam("created_time", $datetime);
                    $stmt->bindParam("modified_time", $datetime);
                    $stmt->bindParam("active_state", $active_state);
                    $result = $stmt->execute();
                } catch(PDOException $e) {
                    echo '{"error":{"text":'. $e->getMessage() .'}}';
                }

                // Check for successful insertion
        if ($result) {
                    $sendEmail = new SendGridEmail();
                    $sendEmail->sendRegistrationEmail($email,$verifyToken);
//                    $mail = new Mail();
//                    $mail->send($email,"Welcome Message","Welcome to Ariya. Visit ".VERIFY_ACCOUNT_URL."?email=".$email."&token=".$verifyToken);
                    // User successfully inserted
                    return REGISTRATION_SUCCESSFUL;
                } else {
                    // Failed to create user
                    return REGISTRATION_FAILED;
                }
            } else {
                // User with same token already exists in the db
                return REGISTRATION_FAILED;
            }
        } else {
            // User with same email already existed in the db
            return EMAIL_ALREADY_EXISTS;
        }
    }

    /**
     * Checking user login
     * @param String $email User login email id
     * @param String $password User login password
     * @return boolean User login status success/fail
     */
    public function checkLogin($email, $password) {
        // fetching user by email
        $sql = "SELECT `password`, `active_status` FROM `users` WHERE `email_address` = :email";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam("email", $email);
            $stmt->execute();
            //$user = $stmt->fetch(PDO::FETCH_ASSOC);
            $num_rows = $stmt->rowCount();
        } catch(PDOException $e) {
            echo '{"error":{"text":'. $e->getMessage() .'}}';
        }
        if ($num_rows > 0) {
            // Found user with the email
            // Now verify the password
            $password_hash = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($password_hash['active_status'] == 2) {
                return USER_NOT_VERIFIED;
            }
            if (PassHash::check_password($password_hash['password'], $password)) {
                // User password is correct
                return LOGIN_SUCCESSFUL;
            } else {
                // user password is incorrect
                return UNSUCCESSFUL_LOGIN;
            }
        } else {
            // user doesn't exist with the email
            return UNSUCCESSFUL_LOGIN;
        }
    }

//
//    public function forgotPassword($email) {
//        require_once 'PassHash.php';
//        require_once '../libs/sendgrid-php/sendgrid-php.php';
//        require_once 'SendGridEmail.php';
//
//
//        $forgotToken = PassHash::emailHash($email,"forgotpassword");
//        $sendEmail = new SendGridEmail();
//        $sendEmail->forgotPasswordEmail($email,$forgotToken);
////        $mail = new Mail();
////        if ($mail->send($email,"Welcome Message","Welcome to Ariya. Visit ".FORGOT_PASSWORD_URL."?email=".$email."&token=".$forgotToken)) {
//        return TRUE;
//    }
//
//    public function resendVerification($email) {
//        require_once 'PassHash.php';
//        require_once '../libs/sendgrid-php/sendgrid-php.php';
//        require_once 'SendGridEmail.php';
//
//        $active = $this->getUserByEmail($email);
//        if ($active['active_status']==2) {
//            $verifyToken = PassHash::emailHash($email,"register");
//            $sendEmail = new SendGridEmail();
//            $sendEmail->verificationEmail($email,$verifyToken);
//            return TRUE;
//        } else {
//            return FALSE;
//        }
//    }


    public function verifyAccount($email) {
        $sql = "UPDATE `users` SET `active_status` = 1 WHERE `email_address` = :email";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam("email", $email);
            $stmt->execute();
            return TRUE;
        } catch(PDOException $e) {
            echo '{"error":{"text":'. $e->getMessage() .'}}';
        }
    }


    /**
     * Checking for duplicate user by email address
     * @param String $email email to check in db
     * @return boolean
     */
    private function isEmailExists($email) {
        $sql = "SELECT `user_id` from `users` WHERE `email_address` = :email";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam("email", $email);
            $stmt->execute();
            $num_rows = $stmt->rowCount();
            return $num_rows > 0;
        } catch(PDOException $e) {
            echo '{"error":{"text":'. $e->getMessage() .'}}';
        }
    }

    /**
     * Checking for duplicate Token
     * @param String $token value to check in db
     * @return boolean
     */
    private function isTokenExists($token) {
        $sql = "SELECT `user_id` from `users` WHERE `token` = :token";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam("token", $token);
            $stmt->execute();
            $num_rows = $stmt->rowCount();
            return $num_rows > 0;
        } catch(PDOException $e) {
            echo '{"error":{"text":'. $e->getMessage() .'}}';
        }
    }

    /**
     * Fetching user by email
     * @param String $email User email id
     */
    public function getUserByEmail($email) {
        $sql = "SELECT `email_address`, `token`, `verification_token`, `created_time`, `active_status` FROM `users` WHERE `email_address` = :email";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam("email", $email);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            return $user;
        } catch(PDOException $e) {
            echo '{"error":{"text":'. $e->getMessage() .'}}';
        }
    }

    public function getUserById($userId) {
        $sql = "SELECT `email_address`, `token`, `profile_picture`, `created_time`, `active_status` FROM `users` WHERE `user_id` = :userId";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam("userId", $userId);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            return $user;
        } catch(PDOException $e) {
            echo '{"error":{"text":'. $e->getMessage() .'}}';
        }
    }

    public function verifyUser($email) {
        $sql = "UPDATE `users` SET `active_status` = 1 WHERE `email_address` = :email";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam("email", $email);
            $stmt->execute();
            return TRUE;
        } catch(PDOException $e) {
            echo '{"error":{"text":'. $e->getMessage() .'}}';
        }
    }

    public function resetPassword($password, $email) {
        require_once 'PassHash.php';
        $password_hash = PassHash::hash($password);
        $sql = "UPDATE `users` SET `password` = :password WHERE `email_address` = :email";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam("password", $password_hash);
            $stmt->bindParam("email", $email);
            $stmt->execute();
            return TRUE;
        } catch(PDOException $e) {
            echo '{"error":{"text":'. $e->getMessage() .'}}';
        }
    }

    /**
     * Generating random Unique MD5 String for User Token
     */
    private function generateToken() {
        return md5(time().uniqid(rand(), TRUE));
    }

    public function isValidToken($token) {
        $sql = "SELECT `user_id` from `users` WHERE `token` = :token";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam("token", $token);
            $stmt->execute();
            $num_rows = $stmt->rowCount();
            return $num_rows > 0;
        } catch(PDOException $e) {
            echo '{"error":{"text":'. $e->getMessage() .'}}';
        }
    }

    public function getUserByToken($token) {
        $sql = "SELECT `user_id` FROM `users` WHERE `token` = :token";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam("token", $token);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            return $user;
        } catch(PDOException $e) {
            echo '{"error":{"text":'. $e->getMessage() .'}}';
        }
    }


    public function getDateId($date) {
        $sql = "SELECT `date_id` FROM `dates` WHERE `date` = :date";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam("date", $date);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            return $user;
        } catch(PDOException $e) {
            echo '{"error":{"text":'. $e->getMessage() .'}}';
        }
    }

    public function vote($date_id, $user_id, $competitor_id) {
        $datetime = date("Y-m-d H:i:s", time());
        $sql = "INSERT INTO votes (`date_id`, `user_id`, `competitor_id`, `created_time`)
                VALUES (:date_id, :user_id, :competitor_id, :time_posted)";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam("date_id", $date_id);
            $stmt->bindParam("user_id", $user_id);
            $stmt->bindParam("competitor_id", $competitor_id);
            $stmt->bindParam("time_posted", $datetime);
            $stmt->execute();
            $vote_id = $this->conn->lastInsertId();


            $sql = "UPDATE `competitors` SET `votes` = `votes` + 1 WHERE `competitor_id` = :competitor_id";
            try {
                $stmt = $this->conn->prepare($sql);
                $stmt->bindParam("competitor_id", $competitor_id);
                $stmt->execute();
//                return TRUE;
            } catch(PDOException $e) {
                echo '{"error":{"text":'. $e->getMessage() .'}}';
            }
            return $vote_id;
            //return TRUE;

        } catch(PDOException $e) {
            echo '{"error":{"text":'. $e->getMessage() .'}}';
        }
    }

    public function hasUserVoted($date_id, $user_id) {
        $sql = "SELECT `vote_id`, `competitor_id` FROM `votes` WHERE `date_id` = :date_id AND `user_id` = :user_id AND `active_status` = 1";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam("date_id", $date_id);
            $stmt->bindParam("user_id", $user_id);
            $stmt->execute();
            $vote = $stmt->fetch(PDO::FETCH_ASSOC);
            $num_rows = $stmt->rowCount();
            return array('voted' => $num_rows > 0, 'voteArr' => $vote);
        } catch(PDOException $e) {
            echo '{"error":{"text":'. $e->getMessage() .'}}';
        }
    }

    public function getCompetitorsByDate($date_id) {
        $sql = "SELECT `competitor_id`, CONCAT('http://eportal.oauife.edu.ng/pic.php?image_id=', matric_no, '20142') AS `img_url`, `votes`, `position` FROM `competitors` WHERE `date_id` = :date_id";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam("date_id", $date_id);
            $stmt->execute();
            $competitors = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $competitors;
        } catch(PDOException $e) {
            echo '{"error":{"text":'. $e->getMessage() .'}}';
        }
    }

    public function doesCompetitorMatchDate($date_id, $competitor_id) {
        $sql = "SELECT `matric_no` from `competitors` WHERE `date_id` = :date_id AND `competitor_id` = :competitor_id AND `active_status` = 1";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam("date_id", $date_id);
            $stmt->bindParam("competitor_id", $competitor_id);
            $stmt->execute();
            $num_rows = $stmt->rowCount();
            return $num_rows > 0;
        } catch(PDOException $e) {
            echo '{"error":{"text":'. $e->getMessage() .'}}';
        }
    }

    public function getTenDatesBelow($date_id) {
        $sql = "SELECT `date_id`, `date` FROM `dates` WHERE `date_id` < :date_id ORDER BY `date_id` DESC LIMIT 10";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam("date_id", $date_id);
            $stmt->execute();
            $date = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $date;
        } catch(PDOException $e) {
            echo '{"error":{"text":'. $e->getMessage() .'}}';
        }
    }


    public function postComment($date_id,$user_id,$comment) {
        $datetime = date("Y-m-d H:i:s", time());
        $sql = "INSERT INTO comments (`date_id`, `user_id`, `comment`, `created_time`)
                VALUES (:date_id, :user_id, :comment, :time_posted)";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam("date_id", $date_id);
            $stmt->bindParam("user_id", $user_id);
            $stmt->bindParam("comment", $comment);
            $stmt->bindParam("time_posted", $datetime);
            $stmt->execute();
            $comment_id = $this->conn->lastInsertId();
            return $comment_id;
        } catch(PDOException $e) {
            echo '{"error":{"text":'. $e->getMessage() .'}}';
        }
    }

    public function getComment($date_id) {
        $sql = "SELECT `comment_id`, `date_id`, comments.user_id, users.first_name, users.last_name, `comment`, comments.created_time FROM `comments`, `users` WHERE `date_id` = :date_id AND comments.user_id = users.user_id AND comments.active_status = 1 ORDER BY comment_id DESC";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam("date_id", $date_id);
            $stmt->execute();
            $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $comments;
        } catch(PDOException $e) {
            echo '{"error":{"text":'. $e->getMessage() .'}}';
        }
    }


    public function getProfileById($id,$dir) {
        $sql = "SELECT `user_id`, `first_name` AS `fname`, `last_name` AS `lname`, `phone_number` AS `phone`, CONCAT(:dir, profile_picture) AS `profile_picture`, `street`, `city`, `state`, `country`, `company_name` AS `cname`, `company_street` AS `cstreet`, `company_city` AS `ccity`, `company_state` AS `cstate`, `company_country` AS `ccountry`, `created_time` FROM `users` WHERE `user_id` =:id";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam("id", $id);
            $stmt->bindParam("dir", $dir);
            $stmt->execute();
            $profile = $stmt->fetch(PDO::FETCH_ASSOC);
            return $profile;
        } catch(PDOException $e) {
            echo '{"error":{"text":'. $e->getMessage() .'}}';
        }
    }


}

?>