<?php 
    require_once '../static/functions/connect.php';
    require_once '../static/functions/function.php';

    require_once '../static/functions/mail/sender.php';

    global $conn;

    if (isset($_POST['reset'])) {
        $email = $_POST['reset'];
        if ($stmt = $conn->prepare("SELECT `id`,`displayname` FROM `user` WHERE email = ?")) {
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 1) {
                $_SESSION['error'] = "ไม่สามารถรีเซ็ตรหัสผ่านได้: พบการใช้อีเมลซ้ำมากกว่า 1 ผู้ใช้งาน โปรดติดต่อผู้ดูแลระบบ";
            } else if ($result->num_rows == 1) {
                while ($row = $result->fetch_assoc()) {
                    $tempAuthKey = generateAuthKey($row['id']);
                    $var = array(
                        "key"=>$tempAuthKey,
                        "email"=>$email,
                        "name"=>$row['displayname']
                    );
                    $sendMail = sendEmail($email, "สวัสดี " . htmlspecialchars($row['displayname']) . "! คุณได้ทำการส่งคำร้องขอรีเซ็ตรหัสผ่านเพื่อเข้าใช้งานเว็บไซต์ grader.pondja.com", "http://grader.pondja.com/static/functions/resetpassword/resetpassword.html", $var);
                    if ($sendMail) {
                        $_SESSION['swal_success'] = "รีเซ็ตรหัสผ่านสำเร็จ";
                        $_SESSION['swal_success_msg'] = "กรุณาตรวจสอบที่อีเมลของท่านเพื่อดำเนินการต่อ";
                    } else {
                        $_SESSION['error'] = "ไม่สามารถรีเซ็ตรหัสผ่านได้: ข้อผิดพลาดภายใน";
                    }
                }
            } else {
                $_SESSION['error'] = "ไม่พบอีเมลนี้ในฐานข้อมูล";
            }
            $stmt->free_result();
            $stmt->close();
        }
    }
    back();
?>