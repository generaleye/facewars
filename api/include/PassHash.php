<?php
/**
 * Class to Hash(Encrypt) the password
 *
 **/
class PassHash {

    // blowfish
    private static $algo = '$2a';
    // cost parameter
    private static $cost = '$10';

    // mainly for internal use
    public static function unique_salt() {
        return substr(sha1(mt_rand()), 0, 22);
    }

    // this will be used to generate a hash
    public static function hash($password) {

        return crypt($password, self::$algo . self::$cost . '$' . self::unique_salt());
    }

    // this will be used to compare a password against a hash
    public static function check_password($hash, $password) {
        $full_salt = substr($hash, 0, 29);
        $new_hash = crypt($password, $full_salt);
        return ($hash == $new_hash);
    }

    // this will be used to generate a hash for the email
    public static function emailHash($email,$append) {
        return md5($email.$append);
    }

    // this will be used to compare an email against a hash
    public static function check_email($hash, $email, $append) {
        $new_hash = md5($email.$append);
        return ($hash == $new_hash);
    }
}

?>
