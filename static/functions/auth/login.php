<?php
    require_once '../connect.php';
    require_once '../function.php';

    $captcha = false;
    if (!isset($_SESSION['page_attempt'])) {
        $_SESSION['page_attempt'] = 0;
    } else {
        if ((int) $_SESSION['page_attempt'] > 5)    $captcha = true;
        else                                        $_SESSION['page_attempt']++;
    }

    function checkCaptcha() {
        $data = array(
            'secret' => "0xE838c11950365625476E7a2d3fD0353F1bd467f0",
            'response' => $_POST['h-captcha-response']
        );

        $verify = curl_init();
        curl_setopt($verify, CURLOPT_URL, "https://hcaptcha.com/siteverify");
        curl_setopt($verify, CURLOPT_POST, true);
        curl_setopt($verify, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($verify, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($verify);
        return json_decode($response)->success;
    }

    if ($captcha == false || ($captcha && checkCaptcha()) ) {
        //Only accept validated captcha.
        if (isset($_POST['method']) && $_POST['method'] == 'loginPage') {
            $user = $_POST['login_username'];
            $pass = md5($_POST['login_password']);

            //ดึงข้อมูลมาเช็คว่า $User ที่ตั้งรหัสผ่านเป็น $Pass มีในระบบรึเปล่า
            $login = login($user, $pass);
            if (!empty($login)) {
                $_SESSION['user'] = $login;
                $_SESSION['swal_success'] = "เข้าสู่ระบบสำเร็จ";
                $_SESSION['swal_success_msg'] = "ยินดีต้อนรับ! " . $login->getName();
            
                if (isset($_POST['method'])) {
                    if ($_POST['method'] == "loginPage") header("Location: ../../../home/");
                    else if ($_POST['method'] == "loginNav") back();
                    else header("Location: ../../../home/");
                } else {
                    back();
                }
            } else {
                $_SESSION['error'] = ErrorMessage::AUTH_WRONG;
                header("Location: ../../../login/");
            }
        } else if (isset($_POST['method']) && $_POST['method'] == 'registerPage') {
            $user = $_POST['register_username'];
            $pass = md5($_POST['register_password']);
            $name = $_POST['register_name'];
            $email = $_POST['register_email'];

            if ($stmt = $conn -> prepare('SELECT id FROM `user` WHERE username = ? OR email = ? LIMIT 1')) {
                $stmt->bind_param('ss', $user, $email);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                    $_SESSION['error'] = "มีชื่อผู้ใช้งานหรืออีเมลนี้อยู่แล้วในระบบ";
                    header("Location: ../../../register/");
                }
                $stmt->free_result();
                $stmt->close();
            } else {
                $_SESSION['error'] = "พบข้อผิดพลาดในการเข้าถึงฐานข้อมูล";
            }
        
            $id = latestIncrement('user');
            if ($stmt = $conn -> prepare("INSERT INTO `user` (id, username, password, displayname, email) VALUES (?,?,?,?,?)")) {
                $stmt->bind_param('issss', $id, $user, $pass, $name, $email);
                if (!$stmt->execute()) {
                    $_SESSION['swal_error'] = "พบข้อผิดพลาด";
                    $_SESSION['swal_error_msg'] = ErrorMessage::DATABASE_QUERY;
                } else {
                    $_SESSION['user'] = login($user, $pass);
                    $_SESSION['swal_success'] = "สมัครสมาชิกสำเร็จ!";
                    $_SESSION['swal_success_msg'] = "ยินดีต้อนรับ " .$_SESSION['user']->getName(). "!";
                }
            } else {
                $_SESSION['error'] = "พบข้อผิดพลาดในการเข้าถึงฐานข้อมูล";
            }

            if (isset($_SESSION['error']))
                header("Location: ../../../register/");
            else
                header("Location: ../../../");    
        }

        if (isset($_GET['user']) && isset($_GET['pass'])) {
            $user = $_GET['user'];
            $pass = md5($_GET['pass']);
            if (isset($_GET['method']) && $_GET['method'] == "reset")
                $pass = $_GET['pass'];

            //ดึงข้อมูลมาเช็คว่า $User ที่ตั้งรหัสผ่านเป็น $Pass มีในระบบรึเปล่า
            $login = login($user, $pass);
            if (!empty($login)) {
                $_SESSION['user'] = $login;
                echo "ACCEPT";
                if (isset($_GET['method'])) {
                    if ($_GET['method'] == "reset") {
                        $_SESSION['allowAccessResetpasswordPage'] = true;
                        header("Location: ../../../resetpassword/");
                    }
                }
            } else {
                $_SESSION['error'] = ErrorMessage::AUTH_WRONG;
                echo ErrorMessage::AUTH_WRONG;
            }
        }
    } else {
        header("Location: ../../../home/");
    }
?>