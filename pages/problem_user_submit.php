<?php
    require_once '../static/functions/connect.php';
    require_once '../static/functions/function.php';
    function acceptFileExtArr($type) {
        switch($type) {
            case "C":
                $accept = array("c", "i");
                break;
            case "Cpp":
                $accept = array("cpp", "cc", "cxx", "c++", "hpp","hh","hxx","h++","h","ii");
                break;
            case "Python":
                $accept = array("py", "rpy", "pyw", "cpy", "gyp", "gypi", "pyi", "ipy");
                break;
            case "Java":
                $accept = array("java", "jav"); 
                break;
            case "TXT":
                $accept = array("txt");
                break;
            default:
                $accept = array();
                break;
        }
        return $accept;
    }
    if (isLogin()) {
        $subID = latestIncrement('submission');
        $userID = $_SESSION['user']->getID();
        $probID = $_POST['probID'];
        $probCodename = $_POST['probCodename'];
        $userCodeLang = $_POST['lang'];
        if (isset($_FILES['submission']['name']) && $_FILES['submission']['name'] != "") {
            $file = $_FILES['submission']['name'];
            $tmp = $_FILES['submission']['tmp_name'];
            
            $ext = pathinfo($file, PATHINFO_EXTENSION);
            if (!in_array($ext, acceptFileExtArr($userCodeLang))) {
                $_SESSION['swal_error'] = "พบข้อผิดพลาด";
                $_SESSION['swal_error_msg'] = "External Exception: นามสกุลไฟล์ไม่ตรงกับภาษา $userCodeLang";
                header("Location: ../problem/$probID");
                die();
            }
            $rename_file = "$subID.$ext";
            $locate ="../file/judge/upload/$userID/";
            print_r($locate);
            if (!file_exists($locate)) {
                if (!mkdir($locate)) die("Can't mkdir");
            }
            if (!move_uploaded_file($tmp,$locate.$rename_file)) die("Can't upload file");
            $userCodeLocate = $locate.$rename_file;

            //INSERT INTO table (id, name, age) VALUES(1, "A", 19) ON DUPLICATE KEY UPDATE name="A", age=19
            if ($stmt = $conn -> prepare("INSERT INTO `submission` (user, problem, lang, script, result) VALUES (?,?,?,?,'W')")) {
                $stmt->bind_param('iiss', $userID, $probID, $userCodeLang, $userCodeLocate);
                if (!$stmt->execute()) {
                    $_SESSION['swal_error'] = "พบข้อผิดพลาด";
                    $_SESSION['swal_error_msg'] = ErrorMessage::DATABASE_QUERY;
                } else {
                    $_SESSION['swal_success'] = "สำเร็จ!";
                    $_SESSION['swal_success_msg'] = "ส่งโค้ตสำเร็จ (Submission #$subID)";
                }
            }

        } else {
            $_SESSION['swal_error'] = "พบข้อผิดพลาด";
            $_SESSION['swal_error_msg'] = "ไม่พบไฟล์";
        }
        header("Location: ../problem/$probID");
    }
?>