<?php

class SendGridEmail {

    private $sendgrid;

    function __construct() {
        $this->sendgrid = new SendGrid(SENDGRID_USERNAME, SENDGRID_PASSWORD);
        $this->mail = new SendGrid\Email();
        // ADD THE APP FILTERS
        $filters = array (
            "templates" => array (
                "settings" => array (
                    "enable" => 1,
                    "template_id" => "de8de4ed-9d33-4a87-aae4-7aca1f2e4667"
                )
            )
        );
        foreach($filters as $filter => $contents) {
            $settings = $contents['settings'];
            foreach($settings as $key => $value) {
                $this->mail->addFilterSetting($filter, $key, $value);
            }
        }
    }

    function content($head, $body, $imageStatus, $imageUrl = NULL) {
        if ($imageStatus) {
            $imageRow = '<tr>
                            <!-- start of image -->
                            <td align="center">
                               <a target="_blank" href="#"><img width="540" border="0" height="282" alt="" style="display:block; border:none; outline:none; text-decoration:none;" src="'.$imageUrl.'" class="bigimage"></a>
                            </td>
                         </tr>';
        } else {
            $imageRow = '';
        }
        $content = '<table width="540" align="center" cellspacing="0" cellpadding="0" border="0" class="devicewidthinner">
                              <tbody>
                                 '.$imageRow.'
                                 <!-- end of image -->
                                 <!-- Spacing -->
                                 <tr>
                                    <td width="100%" height="20"></td>
                                 </tr>
                                 <!-- Spacing -->
                                 <!-- title -->
                                 <tr>
                                    <td style="font-family: Helvetica, arial, sans-serif; font-size: 18px; color: #333333; text-align:left;line-height: 20px;" st-title="rightimage-title">'.$head.'
</td>
                                 </tr>
                                 <!-- end of title -->
                                 <!-- Spacing -->
                                 <tr>
                                    <td width="100%" height="20"></td>
                                 </tr>
                                 <!-- Spacing -->
                                 <!-- content -->
                                 <tr>
                                    <td style="font-family: Helvetica, arial, sans-serif; font-size: 13px; color: #666666; text-align:left;line-height: 24px;" st-content="rightimage-paragraph">'.$body.'
                                    </td>
                                 </tr>
                                 <!-- end of content -->
                                 <!-- Spacing -->
                                 <tr>
                                    <td width="100%" height="10"></td>
                                 </tr>

                                 <!-- /button -->
                                 <!-- Spacing -->
                                 <tr>
                                    <td width="100%" height="20"></td>
                                 </tr>
                                 <!-- Spacing -->
                              </tbody>
                           </table>';
        return $content;
//        <!-- button -->
//         <tr>
//            <td>
//               <table height="30" align="left" valign="middle" border="0" cellpadding="0" cellspacing="0" class="tablet-button" st-button="edit">
//                  <tbody>
//                     <tr>
//                        <td width="auto" align="center" valign="middle" height="30" style=" background-color:#0db9ea; border-top-left-radius:4px; border-bottom-left-radius:4px;border-top-right-radius:4px; border-bottom-right-radius:4px; background-clip: padding-box;font-size:13px; font-family:Helvetica, arial, sans-serif; text-align:center;  color:#ffffff; font-weight: 300; padding-left:18px; padding-right:18px;">
//
//                           <span style="color: #ffffff; font-weight: 300;">
//                              <a style="color: #ffffff; text-align:center;text-decoration: none;" href="#">Read More</a>
//                           </span>
//                        </td>
//                     </tr>
//                  </tbody>
//               </table>
//            </td>
//         </tr>
    }

    /**
     * Send an email after Registration using SendGrid's api
     * @param $recipient
     */

    public function sendRegistrationEmail($recipient,$token) {
        //$url = VERIFY_ACCOUNT_URL."?email=".$recipient."&token=".$token;
//        $emails = new SendGrid\Email();
        $this->mail->addTo($recipient);
        $this->mail->setFrom(SENDGRID_FROM_EMAIL);
        $this->mail->setFromName(SENDGRID_FROM_NAME);
        $this->mail->addCategory('registration');

        try {
            $this->mail->setSubject('Welcome to OAU FaceWars');
            $this->mail->setText('');
            $this->mail->setHtml($this->content(
                'Welcome to OAU FaceWars',
                '<p>Thanks for registering for our service.</p>
                <p>Here is your verification code</p>
                <b>'.$token.'</b><br />
                <p><strong>Thank You!</strong></p><br />', true, 'http://oau-facewars.rhcloud.com/img/bigimage.png'));
            //$response = $this->sendgrid->send($email);
            $this->sendgrid->send($this->mail);
            //var_dump($response);
        } catch ( Exception $e ) {
            echo "Unable to send mail: ", $e->getMessage();
        }

//        $emails
//            ->addTo($recipient)
//            ->setFrom(SENDGRID_FROM_EMAIL)
//            ->setFromName(SENDGRID_FROM_NAME)
//            ->setSubject('Welcome to OAUFACEWARS')
//            ->setHtml('<h1>Welcome to OAUFACEWARS</h1><br />
//                <p>Thanks for registering for our service.</p><br />
//                <p>Here is your verification code</p><br />
//                <b>'.$token.'</b>
//                <p><strong>Thank You!</strong></p><br />')
//        ;
//        //$response = $this->sendgrid->send($email);
//        $this->sendgrid->send($emails);
//        //var_dump($response);
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


//<table width="540" align="center" cellspacing="0" cellpadding="0" border="0" class="devicewidthinner">
//                              <tbody>
//                                 <tr>
//                                    <!-- start of image -->
//                                    <td align="center">
//                                       <a target="_blank" href="#"><img width="540" border="0" height="282" alt="" style="display:block; border:none; outline:none; text-decoration:none;" src="http://oau-facewars.rhcloud.com/img/bigimage.png" class="bigimage"></a>
//                                    </td>
//                                 </tr>
//                                 <!-- end of image -->
//                                 <!-- Spacing -->
//                                 <tr>
//                                    <td width="100%" height="20"></td>
//                                 </tr>
//                                 <!-- Spacing -->
//                                 <!-- title -->
//                                 <tr>
//                                    <td style="font-family: Helvetica, arial, sans-serif; font-size: 18px; color: #333333; text-align:left;line-height: 20px;" st-title="rightimage-title">
//    LOREM IPSUM
//</td>
//                                 </tr>
//                                 <!-- end of title -->
//                                 <!-- Spacing -->
//                                 <tr>
//                                    <td width="100%" height="20"></td>
//                                 </tr>
//                                 <!-- Spacing -->
//                                 <!-- content -->
//                                 <tr>
//                                    <td style="font-family: Helvetica, arial, sans-serif; font-size: 13px; color: #666666; text-align:left;line-height: 24px;" st-content="rightimage-paragraph">
//    Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.
//                                    </td>
//                                 </tr>
//                                 <!-- end of content -->
//                                 <!-- Spacing -->
//                                 <tr>
//                                    <td width="100%" height="10"></td>
//                                 </tr>
//                                 <!-- button -->
//                                 <tr>
//                                    <td>
//                                       <table height="30" align="left" valign="middle" border="0" cellpadding="0" cellspacing="0" class="tablet-button" st-button="edit">
//                                          <tbody>
//                                             <tr>
//                                                <td width="auto" align="center" valign="middle" height="30" style=" background-color:#0db9ea; border-top-left-radius:4px; border-bottom-left-radius:4px;border-top-right-radius:4px; border-bottom-right-radius:4px; background-clip: padding-box;font-size:13px; font-family:Helvetica, arial, sans-serif; text-align:center;  color:#ffffff; font-weight: 300; padding-left:18px; padding-right:18px;">
//
//                                                   <span style="color: #ffffff; font-weight: 300;">
//                                                      <a style="color: #ffffff; text-align:center;text-decoration: none;" href="#">Read More</a>
//                                                   </span>
//                                                </td>
//                                             </tr>
//                                          </tbody>
//                                       </table>
//                                    </td>
//                                 </tr>
//                                 <!-- /button -->
//                                 <!-- Spacing -->
//                                 <tr>
//                                    <td width="100%" height="20"></td>
//                                 </tr>
//                                 <!-- Spacing -->
//                              </tbody>
//                           </table>

?>