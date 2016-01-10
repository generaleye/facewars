<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 6/5/15
 * Time: 4:18 PM
 */


class OauFaceWars
{

    private $conn;
    private $dept = array("CSC","MEE","MSE","EEG", "AGE", "FST", "CVE", "CHE", "LAW", "DSS", "ECN", "GPY", "PSY", "POL", "CHM", "PHY", "MTH", "GLY", "BCH", "BOT", "ZOO","EGL");
    private $year = array(2010,2011,2012,2013,2014);

    function __construct()
    {
        date_default_timezone_set('Africa/Lagos');
        require_once dirname(__FILE__) . '/DbConnect.php';
        // opening db connection
        $db = new DbConnect();
        $this->conn = $db->connect();
    }


    public function insertDate() {

//        $date = "2015-06-20";
        $date = date("Y-m-d", time());
        $datetime = date("Y-m-d H:i:s", time());

        if (!$this->doesDateExist($date)) {
                            // insert query
            $sql = "INSERT INTO dates (`date`, `created_time`) VALUES (:date, :created_time)";
            try {
                $stmt = $this->conn->prepare($sql);
                $stmt->bindParam("date", $date);
                $stmt->bindParam("created_time", $datetime);
                $stmt->execute();
                $date_id = $this->conn->lastInsertId();
                return $date_id;
            } catch(PDOException $e) {
                echo '{"error":{"text":'. $e->getMessage() .'}}';
            }
        } else {
            return NULL;
        }
    }

    public function doesDateExist($date) {
        $sql = "SELECT `date_id` from `dates` WHERE `date` = :date";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam("date", $date);
            $stmt->execute();
            $num_rows = $stmt->rowCount();
            return $num_rows > 0;
        } catch(PDOException $e) {
            echo '{"error":{"text":'. $e->getMessage() .'}}';
        }
    }

    public function getCurrentStatus($date) {
        $sql = "SELECT `date_id`, `current_status` from `dates` WHERE `date` = :date";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam("date", $date);
            $stmt->execute();
            $current = $stmt->fetch(PDO::FETCH_ASSOC);
            return $current;
        } catch(PDOException $e) {
            echo '{"error":{"text":'. $e->getMessage() .'}}';
        }
    }

    public function updateCurrentStatus($date) {
        $current_status = 1;
        $datetime = date("Y-m-d H:i:s", time());
        $sql = "UPDATE `dates` SET `current_status` = :current_status, `modified_time` = :modified WHERE `date` = :date";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam("current_status", $current_status);
            $stmt->bindParam("modified", $datetime);
            $stmt->bindParam("date", $date);
            $stmt->execute();
            return true;
        } catch(PDOException $e) {
            echo '{"error":{"text":'. $e->getMessage() .'}}';
        }
    }

    private function getDateBefore($date_id) {
        $sql = "SELECT `date_id`, `date` FROM `dates` WHERE `date_id` < :date_id ORDER BY `date_id` DESC LIMIT 1";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam("date_id", $date_id);
            $stmt->execute();
            $date = $stmt->fetch(PDO::FETCH_ASSOC);
            return $date;
        } catch(PDOException $e) {
            echo '{"error":{"text":'. $e->getMessage() .'}}';
        }
    }


    private function isUserInBlacklist($matricNo) {
        $sql = "SELECT `matric_no` from `whitelists` WHERE `matric_no` = :matric AND `active_status` = 1";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam("matric", $matricNo);
            $stmt->execute();
            $num_rows = $stmt->rowCount();
            return $num_rows > 0;
        } catch(PDOException $e) {
            echo '{"error":{"text":'. $e->getMessage() .'}}';
        }
    }

    private function isUserInWhitelist($matricNo) {
        $sql = "SELECT `matric_no` from `blacklists` WHERE `matric_no` = :matric AND `active_status` = 1";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam("matric", $matricNo);
            $stmt->execute();
            $num_rows = $stmt->rowCount();
            return $num_rows > 0;
        } catch(PDOException $e) {
            echo '{"error":{"text":'. $e->getMessage() .'}}';
        }
    }

    private function insertIntoCompetitors($date_id, $matricNo, $img_url, $datetime) {

        $sql = "INSERT INTO competitors (`date_id`,`matric_no`, img_url,`created_time`,`modified_time`) VALUES (:date_id, :matric, :img_url, :created, :modified)";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam("date_id", $date_id);
            $stmt->bindParam("matric", $matricNo);
            $stmt->bindParam("img_url", $img_url);
            $stmt->bindParam("created", $datetime);
            $stmt->bindParam("modified", $datetime);
            $stmt->execute();
            $competitor_id = $this->conn->lastInsertId();
            return $competitor_id;
        } catch (PDOException $e) {
            echo '{"error":{"text":' . $e->getMessage() . '}}';
        }
    }

    private function insertIntoBlacklist($matricNo, $competitor_id) {
        $sql = "INSERT INTO blacklists (`matric_no`, `competitor_id`) VALUES (:matric, :competitor)";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam("matric", $matricNo);
            $stmt->bindParam("competitor", $competitor_id);
            $stmt->execute();
            return TRUE;
            //$date_id = $this->conn->lastInsertId();
            //return $date_id;
        } catch (PDOException $e) {
            echo '{"error":{"text":' . $e->getMessage() . '}}';
        }
    }

    public function generateCompetitors($date_id) {
        $date = date("Y-m-d", time());
        $dir = '../../images/'.$date;
        mkdir($dir,0777);
        $dept = $this->dept;
        $year = $this->year;
        $imageArr = array();
        while (count($imageArr)<5) {
            $matricNo = $this->buildMatricNo($dept,$year);
            if (!$this->isUserInBlacklist($matricNo)) {

                if (!$this->isUserInWhitelist($matricNo)) {

                    $url = "http://eportal.oauife.edu.ng/pic.php?image_id=" . $matricNo . "20142";

                    if ($this->checkUrl($url)) {
                        array_push($imageArr, $matricNo);
                        $datetime = date("Y-m-d H:i:s", time());

                        $filename = count($imageArr).".jpg";

//                        $source = "http://eportal.oauife.edu.ng/pic.php?image_id=csc/2010/05120142";
//                        $dest = "../../images/".date("Y-m-d", time())."/uju.jpg";
                        copy($url, $dir."/".$filename);

                        $competitor_id = $this->insertIntoCompetitors($date_id, $matricNo, $date."/".$filename, $datetime);

                        $this->insertIntoBlacklist($matricNo, $competitor_id);


                        echo "<a href='" . $url . "'>" . $url . "</a><br />";
                    }
                }
            }
            //}
        }
        return TRUE;
    }

    public function checkUrl($url) {
        $mime_type = $this->get_url_mime_type($url);
        if ($mime_type == 'image/jpeg') {
            return TRUE;
        }
        return FALSE;
    }

    public function buildMatricNo($dept,$year) {
        $first = $dept[rand(0, count($dept)-1)];
        $second = $year[rand(0, count($year)-1)];
        $third = str_pad(rand(1,100),3,"0",STR_PAD_LEFT);
        $matric = $first."/".$second."/".$third;
        return $matric;
    }

    public function get_url_mime_type($url) {

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_NOBODY, 1);
        curl_exec($ch);
        return curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

    }

    public function rankPreviousDate($date_id) {
        $date_before_arr = $this->getDateBefore($date_id);
        $date_before_id = $date_before_arr['date_id'];

        $datetime = date("Y-m-d H:i:s", time());
        $sql = "SELECT `competitor_id`, `matric_no`, `votes`
                FROM `competitors`
                WHERE `date_id` = :date_id AND `active_status` = 1
                ORDER BY `votes` DESC";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam("date_id", $date_before_id);
            $stmt->execute();
            $competitors = $stmt->fetchAll(PDO::FETCH_ASSOC);
            //return $competitors;

            for ($i=0;$i<5;$i++) {
                $i_ = $i+1;
                echo $competitors[$i]['matric_no'];

                $sql = "UPDATE `competitors` SET `position` = :position, `modified_time` = :modified WHERE `competitor_id` = :competitor";
                try {
                    $stmt = $this->conn->prepare($sql);
                    $stmt->bindParam("position", $i_);
                    $stmt->bindParam("modified", $datetime);
                    $stmt->bindParam("competitor", $competitors[$i]['competitor_id']);
                    $stmt->execute();
                    //return TRUE;
                } catch(PDOException $e) {
                    echo '{"error":{"text":'. $e->getMessage() .'}}';
                }
            }

        } catch(PDOException $e) {
            echo '{"error":{"text":'. $e->getMessage() .'}}';
        }
    }

    public function shuffleImage() {
        $dept = $this->dept;
        $year = $this->year;
        $imageArr = array();
        while (count($imageArr)<1) {
            $matricNo = $this->buildMatricNo($dept,$year);

            if (!$this->isUserInWhitelist($matricNo)) {

                $url = "http://eportal.oauife.edu.ng/pic.php?image_id=" . $matricNo . "20142";
                if ($this->checkUrl($url)) {

                    array_push($imageArr, $matricNo);
                    return $url;

                }

            }
        }
        return TRUE;
    }

    public function cronTest() {
        $datetime = date("Y-m-d H:i:s", time());
        $sql = "INSERT INTO tests (`created_time`)
                VALUES (:created_time)";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam("created_time", $datetime);
            $stmt->execute();
            $test_id = $this->conn->lastInsertId();
            return $test_id;

        } catch(PDOException $e) {
            echo '{"error":{"text":'. $e->getMessage() .'}}';
        }
    }


    public function loadCompetitors($date) {
        $dir = '../../images/'.$date;
        mkdir($dir,0777);
        $dept = $this->dept;
        $year = $this->year;
        $imageArr = array();
        while (count($imageArr)<5) {
            $matricNo = $this->buildMatricNo($dept,$year);
            if (!$this->isUserInBlacklist($matricNo)) {

                if (!$this->isUserInWhitelist($matricNo)) {

                    $url = "http://eportal.oauife.edu.ng/pic.php?image_id=" . $matricNo . "20142";

                    if ($this->checkUrl($url)) {
                        array_push($imageArr, $matricNo);
//                        $datetime = date("Y-m-d H:i:s", time());

                        $filename = count($imageArr).".jpg";

//                        $source = "http://eportal.oauife.edu.ng/pic.php?image_id=csc/2010/05120142";
//                        $dest = "../../images/".date("Y-m-d", time())."/uju.jpg";
                        copy($url, $dir."/".$filename);

//                        $competitor_id = $this->insertIntoCompetitors($date_id, $matricNo, $date."/".$filename, $datetime);
//
//                        $this->insertIntoBlacklist($matricNo, $competitor_id);
//
//
//                        echo "<a href='" . $url . "'>" . $url . "</a><br />";

                    }
                }
            }
            //}
        }
        return $imageArr;
    }

    public function loadCompetition() {
        $datetime = date("Y-m-d H:i:s", time());
        $today = "2016-01-20";//date("Y-m-d", time());
        $datesArr = array();
        array_push($datesArr,$today);
        for ($i = 1; $i <= 8; $i++) {
            $datesArr[$i] = date("Y-m-d", strtotime('+ ' . $i . ' day', strtotime($today)));
        }
        var_dump($datesArr);
        foreach($datesArr as $date) {
            if (!$this->doesDateExist($date)) {
//                echo $date . " not". "</br>";

                $sql = "INSERT INTO dates (`date`, `created_time`) VALUES (:date, :created_time)";
                try {
                    $this->conn->beginTransaction();
                    $stmt = $this->conn->prepare($sql);
                    $stmt->bindParam("date", $date);
                    $stmt->bindParam("created_time", $datetime);
                    $stmt->execute();
                    $date_id = $this->conn->lastInsertId();

                    $matricNos = $this->loadCompetitors($date);
                    for ($j = 1; $j <= count($matricNos); $j++) {
                        $img_url = $date."/".$j.".jpg";

                        $sqlCom = "INSERT INTO competitors (`date_id`,`matric_no`, img_url,`created_time`,`modified_time`) VALUES (:date_id, :matric, :img_url, :created, :modified)";
                        try {
                            $stmt = $this->conn->prepare($sqlCom);
                            $stmt->bindParam("date_id", $date_id);
                            $stmt->bindParam("matric", $matricNos[$j-1]);
                            $stmt->bindParam("img_url", $img_url);
                            $stmt->bindParam("created", $datetime);
                            $stmt->bindParam("modified", $datetime);
                            $stmt->execute();
                            $competitor_id = $this->conn->lastInsertId();

                            $sqlBlack = "INSERT INTO blacklists (`matric_no`, `competitor_id`) VALUES (:matric, :competitor)";
                            try {
                                $stmt = $this->conn->prepare($sqlBlack);
                                $stmt->bindParam("matric", $matricNos[$j-1]);
                                $stmt->bindParam("competitor", $competitor_id);
                                $stmt->execute();



                            } catch (PDOException $e) {

                                echo '{"error":{"text":' . $e->getMessage() . '}}';
                            }

                        } catch (PDOException $e) {
                            echo '{"error":{"text":' . $e->getMessage() . '}}';
                        }

                    }
                    $this->conn->commit();
                } catch(PDOException $e) {
                    $this->conn->rollBack();
                    echo '{"error":{"text":'. $e->getMessage() .'}}';
                }
            }
        }
        return true;

    }

}