<?php declare(strict_types=1);
    require_once 'connect.php';
    require_once 'init.php';

    function latestIncrement($db, $dbdatabase = null) {
        global $conn;
        if ($dbdatabase == null) $dbdtatabase = "grader.ga";
        return mysqli_fetch_array(mysqli_query($conn,"SELECT `AUTO_INCREMENT` FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '$dbdatabase' AND TABLE_NAME = '$db'"), MYSQLI_ASSOC)["AUTO_INCREMENT"];
    }
    
    function make_directory($p) {
        $path = explode("/", $p);
        $stackPath = "";
        for ($i = 0; $i < count($path); $i++) {            
            $stackPath .= $path[$i] . "/";
            if (file_exists($stackPath)) continue;
            mkdir($stackPath, 0777, true);
        }
        return file_exists($stackPath);
    }

    //Remove Directory (delTree) by nbari@dalmp.com
    function remove_directory($dir) {
        $files = array_diff(scandir($dir), array('.','..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? remove_directory("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
    }

    function login(String $username, String $password) {
        global $conn;
        if ($stmt = $conn->prepare("SELECT `id` FROM `user` WHERE username = ? AND password = ? LIMIT 1")) {
            $stmt->bind_param('ss', $username, $password);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    return new User((int) $row['id']);
                }
            }
        }
        return null;
    }

    function isLogin() {
        return isset($_SESSION['user']);
    }

    function isAdmin() {
        if (!isLogin()) return false;
        return $_SESSION['user']->isAdmin();
    }
    
    //checkTime is in second unit as UNIX TIME FORMAT.
    function checkAuthKey(String $authKey, int $checkTime = 0, int $uid = 0) {
        global $conn;
        if ($stmt = $conn->prepare("SELECT `tempAuthKey` FROM `user` WHERE `id` = ?")) {
            $stmt->bind_param('i',$uid);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    if (empty($row['tempAuthKey']) || $row['tempAuthKey'] == null) return false;
                    $key = json_decode($row['tempAuthKey'], true);
                    if ($authKey == $key['key']) {
                        if ($checkTime > 0) return ((time() - ((int)$key['time'])) <= $checkTime);
                        else                return true;
                    } else {
                        return false;
                    }
                }
            }
        }
    }

    function generateAuthKey(int $uid) {
        global $conn;

        if (!isValidUserID($uid)) return false;

        $authKey = array(
            "key" => generateRandom(8),
            "time" => time()
        );
        $tempAuthKey = json_encode($authKey);

        if ($stmt = $conn->prepare("UPDATE `user` SET `tempAuthKey` = ? WHERE `id` = ?")) {
            $stmt->bind_param('si',$tempAuthKey,$uid);
            if ($stmt->execute()) {
                return $authKey['key'];
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    function useAuthKey(String $authKey, int $checkTime = 0, int $uid = 0) {
        global $conn;
        if (checkAuthKey($authKey, $checkTime, $uid)) {
            if ($stmt = $conn->prepare("UPDATE `user` SET `tempAuthKey` = null WHERE `id` = ?")) {
                $stmt->bind_param('i',$uid);
                return $stmt->execute();
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    function getUserData(int $id) {
        global $conn;
        if ($stmt = $conn->prepare('SELECT * FROM `user` WHERE id = ?')) {
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    return $row;
                }
            }
        }
        return null;
    }

    function getProbData(int $id) {
        global $conn;
        if ($stmt = $conn->prepare('SELECT * FROM `problem` WHERE id = ?')) {
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    return $row;
                }
            }
        }
        return null;
    }

    function countCategory($category) {
        global $conn;
        if ($stmt = $conn-> prepare("SELECT count(id) AS cat FROM `editorial` WHERE JSON_EXTRACT(`properties`,'$.hide') = false AND JSON_EXTRACT(`properties`,'$.category') = ? ORDER BY JSON_EXTRACT(`properties`,'$.last_hide_updated')")) {
            $stmt->bind_param('s', $category);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows == 1) {
                while ($row = $result->fetch_assoc()) {
                    return $row["cat"];
                }
            }
        }
    }

    function isDarkmode() {
        return (isset($_SESSION['dark_mode'])) ? $_SESSION['dark_mode'] : false;
    }

    function isValidUserID($id) {
        global $conn;
        if ($stmt = $conn->prepare("SELECT `id` FROM `user` WHERE `id` = ? LIMIT 1")) {
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows == 1) {
                return true;
            }
        }
        return false;
    }

    function isValidProbID($id) {
        global $conn;
        if ($stmt = $conn->prepare("SELECT `id` FROM `problem` WHERE `id` = ? LIMIT 1")) {
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows == 1) {
                return true;
            }
        }
        return false;
    }

    function rating($rate) {
        switch($rate) {
            case 0:
                return "<text class='light-blue-text'>Peaceful</text>";
            case 1:
                return "<text class='text-primary'>Easy</text>";
            case 2:
                return "<text class='text-success'>Normal</text>";
            case 3:
                return "<text class='text-warning'>Hard</text>";
            case 4:
                return "<text class='text-danger'>Insane</text>";
            case 5:
                return "<text class='pink-text font-weight-bold'>Merciless</text>";
            default:
                return "<text class='text-muted'>Unrated</text>";
        }
    }

    function isPassed($uID, $pID) {
        $arr = lastSubmission($uID,$pID);
        if (!$arr) return 0; //Case not any submission yet.
        if ($arr['score'] == $arr['maxScore']) return 1; //Case full score.
        if ($arr['score'] != 0 && $arr['score'] < $arr['maxScore']) return -1;
    }

    function lastResult($uID, $pID) {
        $arr = lastSubmission($uID,$pID);
        if (!$arr) return " "; //Case not any submission yet.
        if ($arr['result'] == "W") return "<text data-wait=true data-sub-id=" . $arr['subID']. ">รอผลตรวจ...</text>";
        return $arr['result'] . " (" . $arr['maxScore'] != 0 ? ($arr['score']/$arr['maxScore'])*$arr['probScore'] : "UNDEFINED" . ")";
    }

    function lastSubmission($uID, $pID) {
        global $conn;
        if (!isValidUserID($uID) || !isValidProbID($pID)) return 0;
        if ($stmt = $conn->prepare("SELECT `submission`.`id` as subID, `submission`.`result` AS result,`submission`.`score` AS score,`submission`.`maxScore` AS maxScore,`problem`.`score` AS probScore FROM `submission` INNER JOIN `problem` ON `submission`.`problem` = `problem`.`id` WHERE problem = ? AND user = ? ORDER BY `submission`.`id` DESC limit 1")) {
            $stmt->bind_param('ii', $pID, $uID);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows == 1) {
                while ($row = $result->fetch_assoc()) {
                    $arr = array();
                    $arr["subID"] = $row['subID'];
                    $arr["score"] = $row['score'];
                    $arr["maxScore"] = $row['maxScore'];
                    $arr["result"] = $row['result'];
                    $arr["probScore"] = $row['probScore'];
                    return $arr;
                }
                $stmt->free_result();
                $stmt->close();  
            } else {
                return 0;
            }
        } 
    }

    function countScore($result, $full = 100) {
        return number_format((float) count_chars(strtoupper($result))[80]*($full/strlen($result)), 2, '.', '');
    }

    function randomLoading() {
        $targetDir = "light";
        if (isDarkmode()) $targetDir = "dark";
        $files = glob("../static/elements/loading/$targetDir/*.*", GLOB_BRACE);
        return $files[rand(0,count($files)-1)];
    }
?>
<?php
    function getClientIP() {
        if(!empty($_SERVER['HTTP_CLIENT_IP'])) return $_SERVER['HTTP_CLIENT_IP'];
        else if(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) return $_SERVER['HTTP_X_FORWARDED_FOR'];
        return $_SERVER['REMOTE_ADDR'];
    }

    function randomErrorMessage() {
        $message = array(
            "(╯°□°）╯︵ ┻━┻",
            "┬─┬ ノ( ゜-゜ノ)",
            "ლ(ಠ益ಠლ)",
            "¯\_(ツ)_/¯",
            "‎(ﾉಥ益ಥ）ﾉ ┻━┻",
            "┬┴┬┴┤(･_├┬┴┬┴",
            "ᕙ(⇀‸↼‶)ᕗ",
            "(づ｡◕‿‿◕｡)づ",
            "(ノ^_^)ノ┻━┻ ┬─┬ ノ( ^_^ノ)",
            "(⌐■_■)","─=≡Σ(([ ⊐•̀⌂•́]⊐",
            "(　-_･)σ - - - - - - - - ･",
            "┌( ಠ_ಠ)┘",
            "♪ (｡´＿●`)ﾉ┌iiii┐ヾ(´○＿`*) ♪",
            "ᕙ( ͡° ͜ʖ ͡°)ᕗ",
            "(ÒДÓױ)"
        );
        return $message[rand(0,count($message)-1)];
    }

    function path_curTime() {
        date_default_timezone_set('Asia/Bangkok'); return date('Y/m/d', time());
    }

    function unformat_curTime() {
        date_default_timezone_set('Asia/Bangkok'); return date('YmdHis', time());
    }

    function curDate() {
        date_default_timezone_set('Asia/Bangkok'); return date('Y-m-d', time());
    }

    function curTime() {
        date_default_timezone_set('Asia/Bangkok'); return date('H:i:s', time());
    }

    function curFullTime() {
        date_default_timezone_set('Asia/Bangkok'); return date('Y-m-d H:i:s', time());
    }

    function sendFileToIMGHost($file) {
        $data = array(
            'img' => new CURLFile($file['tmp_name'],$file['type'], $file['name']),
        ); 
        
        //**Note :CURLFile class will work if you have PHP version >= 5**
        
         $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, 'https://img.p0nd.dev/upload.php');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_TIMEOUT, 86400); // 1 Day Timeout
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60000);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_REFERER, $_SERVER['HTTP_HOST']);
        
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            $msg = FALSE;
        } else {
            $msg = $response;
        }
        
        curl_close($ch);
        return $msg;
    }

    function generateRandom($length = 16) {
        $characters = md5((string) time());
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }
?>

<?php
    function needLogin() {
    if (!isLogin()) {?>
<script>
    swal({
        title: "ACCESS DENIED",
        text: "You need to logged-in!",
        icon: "error"
    }).then(function () {
        <?php $_SESSION['error'] = "กรุณาเข้าสู่ระบบก่อนดำเนินการต่อ"; ?>
        window.location = "../login/";
    });
</script>
<?php die(); }} ?>

<?php
    function needAdmin() {
        global $conn;
    if (!isLogin()) { needLogin(); die(); return false; }
    if (!isAdmin()) { ?>
<script>
    swal({
        title: "ACCESS DENIED",
        text: "You don't have enough permission!",
        icon: "warning"
    });
</script>
<?php die(); return false;}
        return true;
    }
?>
<?php function back() {
    if (isset($_SERVER["HTTP_REFERER"])) {
        header("Location: " . $_SERVER["HTTP_REFERER"]);
    } else {
        home();
    }
    die();
    } ?>
<?php function home() {
    header("Location: ../home/");
} ?>
<?php function logout() { ?>
    <script>
        swal({
            title: "ออกจากระบบ ?",
            text: "คุณต้องการออกจากระบบหรือไม่?",
            icon: "warning",
            buttons: true,
            dangerMode: true}).then((willDelete) => {
                if (willDelete) {
                    window.location = "../logout/";
                }
            });
</script>
<?php } ?>

<?php function deletePost($id) { ?>
    <script>
        swal({
            title: "ลบข่าวหรือไม่ ?",
            text: "หลังจากที่ลบแล้ว ข่าวนี้จะไม่สามารถกู้คืนได้!",
            icon: "warning",
            buttons: true,
            dangerMode: true}).then((willDelete) => {
                if (willDelete) {
                    window.location = "../post/delete.php?id=<?php echo $id; ?>";
                }
            });
    </script>
<?php } ?>
<?php function warningSwal($title,$name) { ?>
    <script>
    swal({
        title: "<?php echo $title; ?>",
        text: "<?php echo $name; ?>",
        icon: "warning"
    });
    </script>
<?php } ?>
<?php function errorSwal($title,$name) { ?>
    <script>
    swal({
        title: "<?php echo $title; ?>",
        text: "<?php echo $name; ?>",
        icon: "error"
    });
    </script>
<?php } ?>
<?php function successSwal($title,$name) { ?>
    <script>
    swal({
        title: "<?php echo $title; ?>",
        text: "<?php echo $name; ?>",
        icon: "success"
    });
    </script>
<?php } ?>
<?php function debug($message) { echo $message; } ?>

<?php
    function startsWith($haystack, $needle) {
        return substr_compare($haystack, $needle, 0, strlen($needle)) === 0;
    }
    function endsWith($haystack, $needle) {
        return substr_compare($haystack, $needle, -strlen($needle)) === 0;
    }
    
    if (isLogin() && !isValidUserID($_SESSION['user']->getID())) {
        session_destroy();
        header("Location: ../home/");
    }
?>
