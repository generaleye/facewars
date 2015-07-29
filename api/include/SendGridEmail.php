<?php

class SendGridEmail {

    private $sendgrid;

    function __construct() {
        $this->sendgrid = new SendGrid(SENDGRID_USERNAME, SENDGRID_PASSWORD);
    }


    /**
     * Send an email after Registration using SendGrid's api
     * @param $recipient
     */

    public function sendRegistrationEmail($recipient,$token) {
        //$url = VERIFY_ACCOUNT_URL."?email=".$recipient."&token=".$token;
        $emails = new SendGrid\Email();
        $emails
            ->addTo($recipient)
            ->setFrom(SENDGRID_FROM_EMAIL)
            ->setFromName(SENDGRID_FROM_NAME)
            ->setSubject('Welcome to OAUFACEWARS')
            ->setHtml('<h1>Welcome to OAUFACEWARS</h1><br />
                <p>Thanks for registering for our service.</p><br />
                <p>Here is your verification code</p><br />
                <b>'.$token.'</b>
                <p><strong>Thank You!</strong></p><br />')
        ;
        //$response = $this->sendgrid->send($email);
        $this->sendgrid->send($emails);
        //var_dump($response);
    }


    public function forgotPasswordEmail($recipient,$forgotToken) {
        $url = FORGOT_PASSWORD_URL."?email=".$recipient."&token=".$forgotToken;
        $emails = new SendGrid\Email();
        $emails
            ->addTo($recipient)
            ->setFrom(SENDGRID_FROM_EMAIL)
            ->setFromName(SENDGRID_FROM_NAME)
            ->setSubject('Forgot Password')
            ->setHtml('<h1>Dear User,</h1><br />
                <p>here was recently a request to change the password for your account.</p>
                <p>If you requested this password change, please click on the following link to reset your password:</p>
                <p><a href="'.$url.'">LINK</a></p>
                <p>URL: '.$url.'</p>
                <p>If clicking the link does not work, please copy and paste the URL into your browser instead.</p>
                <p>If you did not make this request, you can ignore this message and your password will remain the same.</p><br />')
        ;
        //$response = $this->sendgrid->send($email);
        $this->sendgrid->send($emails);
        //var_dump($response);
    }


    public function verificationEmail($recipient,$token) {
        $url = VERIFY_ACCOUNT_URL."?email=".$recipient."&token=".$token;
        $emails = new SendGrid\Email();
        $emails
            ->addTo($recipient)
            ->setFrom(SENDGRID_FROM_EMAIL)
            ->setFromName(SENDGRID_FROM_NAME)
            ->setSubject('Verification Email')
            ->setHtml('<h1>Dear User,</h1><br />
                <p>Visit this <a href="'.$url.'">LINK</a> to verify your account.</p>
                <p>URL: '.$url.'</p>
                <p><strong>Thank You!</strong></p><br />')
        ;
        //$response = $this->sendgrid->send($email);
        $this->sendgrid->send($emails);
        //var_dump($response);
    }
}

?>