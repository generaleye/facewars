<?php
ini_set("display_errors",1);
header('Access-Control-Allow-Origin: *');
require_once '../include/DbHandler.php';
require_once '../include/PassHash.php';
require_once '../include/OauFaceWars.php';
require '../libs/Slim/Slim.php';

define('ROOT_DIR', dirname(dirname(dirname(__FILE__))));

\Slim\Slim::registerAutoloader();
$app = new \Slim\Slim();

// User Id from db - Global Variable
//$userId = NULL;

/**
 * For methods that require Auth
 * Adding Middle Layer to authenticate every request
 * Checking if the request has valid token as part of the parameters
 * @param \Slim\Route $route
 * @throws \Slim\Exception\Stop
 */
function authenticate(\Slim\Route $route) {
    // Getting request headers
    //$headers = apache_request_headers();
    $response = array();
    $app = \Slim\Slim::getInstance();
    $token = $app->request->params('token');

    // Verifying Authorization Header
    if (isset($token)) {
        $db = new DbHandler();
        if (!$db->isValidToken($token)) {
            // Token is not present in users table
            $response["error"] = TRUE;
            $response["message"] = "Access Denied. Invalid Token";
            echoResponse(401, $response);
            $app->stop();
        } else {
            global $userId;
            // get user primary key id
            $tox = $db->getUserByToken($token);
            $userId = $tox['user_id'];
        }
    } else {
        // Token is missing in header
        $response["error"] = TRUE;
        $response["message"] = "Token is missing";
        echoResponse(400, $response);
        $app->stop();
    }
}

/**
 * REGISTER, LOGIN AND SIGN-IN FUNCTIONS
 **/
$app->post('/register', function() use ($app) {
    // check for required params
    verifyRequiredParams(array('email','password'));

    $response = array();
    $req = $app->request(); // Getting parameters
    // reading post params
//    $first_name = $req->params('fname');
//    $last_name = $req->params('lname');
    $email = $req->params('email');
    $password = $req->params('password');
    // validating email address
    validateEmail($email);

    $db = new DbHandler();
    $res = $db->createUser("", "", $email, $password);

    if ($res == REGISTRATION_SUCCESSFUL) {
        $user = $db->getUserByEmail($email);

        if ($user != NULL) {
            $response["error"] = FALSE;
            $response['email_address'] = $user['email_address'];
            $response['is_verified'] = FALSE;
            $response['created_time'] = $user['created_time'];
            $response["message"] = "Registration Successful";
        } else {
            // unknown error occurred
            $response['error'] = TRUE;
            $response['message'] = "An error occurred. Please try again";
        }
    } else if ($res == REGISTRATION_FAILED) {
        $response["error"] = TRUE;
        $response["message"] = "Oops! An error occurred while registering";
    } else if ($res == EMAIL_ALREADY_EXISTS) {
        $response["error"] = TRUE;
        $response["message"] = "Sorry, this Email Address already exists";
    }
    // echo json response
    echoResponse(200, $response);
});

$app->post('/login', function() use ($app) {
    // check for required params
    verifyRequiredParams(array('email', 'password'));

    // reading post params
    $req = $app->request();

    $email = $req->params('email');
    $password = $req->params('password');
    // validating email address
    validateEmail($email);
    $response = array();

    $db = new DbHandler();
    // check for correct email and password
    $login = $db->checkLogin($email, $password);
    if ($login == LOGIN_SUCCESSFUL) {
        // get the user by email
        $user = $db->getUserByEmail($email);

        if ($user != NULL) {
            $response["error"] = FALSE;
            $response['email_address'] = $user['email_address'];
            $response['token'] = $user['token'];
            $response['is_verified'] = TRUE;
            $response['created_time'] = $user['created_time'];
            $response['message'] = "Login Successful";
        } else {
            // unknown error occurred
            $response['error'] = TRUE;
            $response['message'] = "An error occurred. Please try again";
        }
    } elseif ($login == USER_NOT_VERIFIED) {
        // user not verified
        $response['error'] = TRUE;
        $response['is_verified'] = FALSE;
        $response['message'] = 'You have not been Verified';
    } else {
        // user credentials are wrong
        $response['error'] = TRUE;
        $response['message'] = 'Incorrect Email Address and/or Password.';
    }

    echoResponse(200, $response);
});

$app->post('/signin', function() use ($app) {
    // check for required params
    verifyRequiredParams(array('email'));

    $response = array();
    $req = $app->request(); // Getting parameters
    // reading post params
    $first_name = ""; //$req->params('fname');
    $last_name = ""; //$req->params('lname');
    $email = $req->params('email');
    $password = "openshift"; //$req->params('password');
    // validating email address
    validateEmail($email);

    $db = new DbHandler();

    $login = $db->checkLogin($email, $password);
    if ($login == LOGIN_SUCCESSFUL) {
        // get the user by email
        $user = $db->getUserByEmail($email);

        if ($user != NULL) {
            $response["error"] = FALSE;
            $response['state'] = 'sign_in';
            $response['email_address'] = $user['email_address'];
            $response['token'] = $user['token'];
            $response['is_verified'] = TRUE;
            $response['created_time'] = $user['created_time'];
            $response['message'] = "Signin Successful";
        } else {
            // unknown error occurred
            $response['error'] = TRUE;
            $response['message'] = "An error occurred. Please try again";
        }
    } elseif ($login == USER_NOT_VERIFIED) {
        // user not verified
        $response['error'] = TRUE;
        $response['state'] = 'not_verified';
        $response['is_verified'] = FALSE;
        $response['message'] = 'You have not been Verified';
    } else {
        $res = $db->createUser($first_name, $last_name, $email, $password);
        if ($res == REGISTRATION_SUCCESSFUL) {
            $user = $db->getUserByEmail($email);

            if ($user != NULL) {
                $response["error"] = FALSE;
                $response['state'] = 'register';
                $response['email_address'] = $user['email_address'];
                $response['is_verified'] = FALSE;
                $response['created_time'] = $user['created_time'];
                $response["message"] = "Registration Successful";
            } else {
                // unknown error occurred
                $response['error'] = TRUE;
                $response['message'] = "An error occurred. Please try again";
            }
        } else if ($res == REGISTRATION_FAILED) {
            $response["error"] = TRUE;
            $response["message"] = "Oops! An error occurred while registering";
        }
    }

    // echo json response
    echoResponse(200, $response);
});

$app->post('/verify', 'verifyAccount');
$app->get('/today', 'today');
$app->get('/leaderboard', 'leaderboard');
$app->get('/competition', 'competition');
$app->post('/vote', 'authenticate', 'vote');
$app->get('/shuffle', 'shuffleImg');
$app->post('/comment', 'authenticate', 'postComment');
$app->get('/comment', 'getComment');
$app->post('/contact', 'contactUs');


$app->run();


function verifyAccount() {
    $app = \Slim\Slim::getInstance();
    verifyRequiredParams(array('email','code'));
    $req = $app->request(); // Getting parameters
    $email = $req->params('email');
    $code = $req->params('code');
    $response = array();

    $db = new DBHandler();
    $user = $db->getUserByEmail($email);
    $verifyCode = $user['verification_token'];

    if ($verifyCode == $code) {
        $verify = $db->verifyAccount($email);

        if ($verify == TRUE) {
            $response["error"] = FALSE;
            $response['message'] = "Your account has been Verified";
        } else {
            // unknown error occurred
            $response['error'] = TRUE;
            $response['message'] = "An error occurred. Please try again";
        }
    } else {
        $response['error'] = TRUE;
        $response['message'] = "Verification Code is Incorrect";
    }

    echoResponse(200, $response);
}

function today() {
    $app = \Slim\Slim::getInstance();
    $req = $app->request(); // Getting parameters
    $token = $req->params('token');

    //Get current date
    $date = date("Y-m-d", time());
    //$date = '2015-06-10';

    $response = array();

    $db = new DBHandler();
    $getDate = $db->getDateId($date);
    $dateId = $getDate['date_id'];
    $response['error'] = FALSE;
    $response['date'] = $date;
    $response['date_id'] = $dateId;

    if ($dateId != NULL) {
        if (isset($token)) {
            $tox = $db->getUserByToken($token);
            $userId = $tox['user_id'];
            $voteQues = $db->hasUserVoted($dateId, $userId);
            if ($voteQues['voted']) {
                $response['voted'] = TRUE;
                $response['competitor_id'] = $voteQues['voteArr']['competitor_id'];
            } else {
                $response['voted'] = FALSE;
            }
        } else {
            $response['voted'] = FALSE;
        }

        $response['competitors'] = $db->getCompetitorsByDate($dateId,IMAGE_URL);

    } else {
        $response['error'] = TRUE;
        $response['message'] = "Computation in progress";
    }


    echoResponse(200, $response);
}


function leaderboard() {
    $app = \Slim\Slim::getInstance();
    $req = $app->request(); // Getting parameters
    $token = $req->params('token');
    $marker = $req->params('marker');

    //Get current date
    $date = date("Y-m-d", time());
    //$date = '2015-06-19';

    $response = array();

    $db = new DBHandler();
    $getDate = $db->getDateId($date);
    $dateId = $getDate['date_id'];

    $response['error'] = FALSE;
    $response['date'] = $date;
    $response['date_id'] = $dateId;
    if (isset($marker)) {
        $dateId = $marker;
    }
    if ($dateId != NULL) {
        $dateArr = $db->getTenDatesBelow($dateId);
        for ($i=0; $i<count($dateArr); $i++) {
            if (isset($token)) {
                $tox = $db->getUserByToken($token);
                $userId = $tox['user_id'];
                $voteQues = $db->hasUserVoted($dateArr[$i]['date_id'], $userId);
                if ($voteQues['voted']) {
                    $response['competitions'][$i]['voted'] = TRUE;
                    $response['competitions'][$i]['competitor_id'] = $voteQues['voteArr']['competitor_id'];
                } else {
                    $response['competitions'][$i]['voted'] = FALSE;
                }
            } else {
                $response['competitions'][$i]['voted'] = FALSE;
            }
            $response['competitions'][$i]['date'] = $dateArr[$i]['date'];
            $response['competitions'][$i]['date_id'] = $dateArr[$i]['date_id'];
            $response['competitions'][$i]['competitors'] = $db->getCompetitorsByDate($dateArr[$i]['date_id'],IMAGE_URL);
        }


    } else {
        $response['error'] = TRUE;
        $response['message'] = "Computation in progress";
    }


    echoResponse(200, $response);
}

function competition() {
    $app = \Slim\Slim::getInstance();
    verifyRequiredParams(array('date_id'));
    $req = $app->request(); // Getting parameters
    $token = $req->params('token');
    $dateId = $req->params('date_id');

    //Get current date
    $date = date("Y-m-d", time());
    //$date = '2015-06-19';

    $response = array();

    $db = new DBHandler();
    $getToday = $db->getDateId($date);
    $todayId = $getToday['date_id'];

    if ($todayId != $dateId) {
        $response['error'] = FALSE;
//    $response['date'] = $date;
        $response['date_id'] = $dateId;

        if (isset($token)) {
            $tox = $db->getUserByToken($token);
            $userId = $tox['user_id'];
            $voteQues = $db->hasUserVoted($dateId, $userId);
            if ($voteQues['voted']) {
                $response['voted'] = TRUE;
                $response['competitor_id'] = $voteQues['voteArr']['competitor_id'];
            } else {
                $response['voted'] = FALSE;
            }
        } else {
            $response['voted'] = FALSE;
        }
        $response['competition'] = $db->getCompetitorsByDate($dateId,IMAGE_URL);
        $response['comments'] = $db->getComment($dateId);
    } else {
        $response['error'] = TRUE;
        $response['message'] = 'An Error Occurred';
    }

    echoResponse(200, $response);
}

function vote() {
    $app = \Slim\Slim::getInstance();
    verifyRequiredParams(array('competitor_id'));
    $req = $app->request(); // Getting parameters
    $token = $req->params('token');
    $competitor_id = $req->params('competitor_id');

    //Get current date
    $date = date("Y-m-d", time());
//    $date = '2015-06-11';

    $response = array();

    $db = new DBHandler();
    $getDate = $db->getDateId($date);
    $dateId = $getDate['date_id'];


    if ($dateId != NULL) {

        if ($db->doesCompetitorMatchDate($dateId, $competitor_id)) {
            $tox = $db->getUserByToken($token);
            $userId = $tox['user_id'];
            $voteQues = $db->hasUserVoted($dateId, $userId);
            if (!$voteQues['voted']) {
                $vote = $db->vote($dateId, $userId, $competitor_id);
                if ($vote != NULL) {
                    $response['error'] = FALSE;
                    $response['vote_id'] = $vote;
                    $response['message'] = "Vote Successful";
                } else {
                    $response['error'] = TRUE;
                    $response['message'] = "An error occurred. Please try again";
                }
            } else {
                $response['error'] = TRUE;
                $response['message'] = "You have already voted";
            }
        } else {
            $response['error'] = TRUE;
            $response['message'] = "Competitor Doesn't Match Date";
        }



    } else {
        $response['error'] = TRUE;
        $response['message'] = "Computation in progress";
    }


    echoResponse(200, $response);
}


function shuffleImg() {
    $response = array();
//    $croned = new OauFaceWars();
//    $shuffled_img = $croned->shuffleImage();
    $db = new DBHandler();

    $random = $db->getRandomCompetitor(IMAGE_URL);
    if ($random != NULL) {
        $response['error'] = FALSE;
        $response['img_url'] = $random;
    } else {
        $response['error'] = TRUE;
        $response['message'] = "An error occurred. Please try again";
    }
//    $response['error'] = FALSE;
//    $response['img_url'] = $shuffled_img;

    echoResponse(200, $response);
}


function postComment() {
    $app = \Slim\Slim::getInstance();
    verifyRequiredParams(array('date_id','comment'));
    $req = $app->request(); // Getting parameters
    $token = $req->params('token');
    $dateId = $req->params('date_id');
    $comment = $req->params('comment');

    //Get current date
    //$date = date("Y-m-d", time());
    //$date = '2015-06-11';

    $response = array();

    $db = new DBHandler();
    $tox = $db->getUserByToken($token);
    $userId = $tox['user_id'];

    $comment_id = $db->postComment($dateId,$userId,$comment);
    if ($comment_id != NULL) {
        $response['error'] = FALSE;
        $response['comment_id'] = $comment_id;
        $response['message'] = "Comment has been Posted Successfully";
    } else {
        $response['error'] = TRUE;
        $response['message'] = "An error occurred. Please try again";
    }

    echoResponse(200, $response);
}

function getComment() {
    $app = \Slim\Slim::getInstance();
    verifyRequiredParams(array('date_id'));
    $req = $app->request(); // Getting parameters
    $dateId = $req->params('date_id');

    $response = array();
    $db = new DBHandler();


    $comments = $db->getComment($dateId);
    if ($comments != NULL) {
        $response['error'] = FALSE;
        $response['date_id'] = $dateId;
        $response['comments'] = $comments;
    } else {
        $response['error'] = TRUE;
        $response['message'] = "An error occurred. Please try again";
    }

    echoResponse(200, $response);
}


function contactUs() {
    $app = \Slim\Slim::getInstance();
    verifyRequiredParams(array('name', 'email','message'));
    $req = $app->request(); // Getting parameters
    $token = $req->params('token');
    $name = $req->params('name');
    $email = $req->params('email');
    $message = $req->params('message');
    validateEmail($email);

    //Get current date
    //$date = date("Y-m-d", time());
    //$date = '2015-06-11';

    $response = array();

    $db = new DBHandler();

    if (isset ($token)) {
        $tox = $db->getUserByToken($token);
        $userId = $tox['user_id'];
    } else {
        $userId = "";
    }


    $message_id = $db->contactUs($name,$email,$userId,$message);
    if ($message_id != NULL) {
        $response['error'] = FALSE;
        $response['contact_id'] = $message_id;
        $response['message'] = "Message has been Posted Successfully";
    } else {
        $response['error'] = TRUE;
        $response['message'] = "An error occurred. Please try again";
    }

    echoResponse(200, $response);
}


/**
 * Verifying required params posted or not
 * @param $required_fields
 * @throws \Slim\Exception\Stop
 */
function verifyRequiredParams($required_fields) {
    $error = FALSE;
    $error_fields = "";
    //$request_params = array();
    $request_params = $_REQUEST;
    // Handling PUT request params
    if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
        $app = \Slim\Slim::getInstance();
        parse_str($app->request()->getBody(), $request_params);
    }
    foreach ($required_fields as $field) {
        if (!isset($request_params[$field]) || strlen(trim($request_params[$field])) <= 0) {
            $error = TRUE;
            $error_fields .= $field . ', ';
        }
    }

    if ($error) {
        // Required field(s) are missing or empty
        // echo error json and stop the app
        $response = array();
        $app = \Slim\Slim::getInstance();
        $response["error"] = TRUE;
        $response["message"] = 'Required field(s) ' . substr($error_fields, 0, -2) . ' is missing or empty';
        echoResponse(400, $response);
        $app->stop();
    }
}

/**
 * Validating email address
 * @param $email
 * @throws \Slim\Exception\Stop
 */
function validateEmail($email) {
    $app = \Slim\Slim::getInstance();
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response["error"] = TRUE;
        $response["message"] = 'Email address is not valid';
        echoResponse(400, $response);
        $app->stop();
    }
}

function echoResponse($status_code, $response) {
    $app = \Slim\Slim::getInstance();
    // Http response code
    $app->status($status_code);

    // setting response content type to json
    $app->contentType('application/json');

    echo json_encode($response);
}

?>
