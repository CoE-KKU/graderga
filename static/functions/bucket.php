<?php
    require_once 'init.php';
    $filename = "../../".bucket_decrypting(str_replace(" ","+",$_GET['target']));
    $allowanceUserID = -1;
    if (preg_match('/\/*file\/judge\/upload\/([0-9]+)\/*/', $filename, $matches))
        $allowanceUserID = $matches[1];

    if (file_exists($filename) && isLogin() && (isAdmin() || $_SESSION['user']->getID() == $allowanceUserID) && finfo_open(FILEINFO_MIME_TYPE) !== false) {
        //Get file type and set it as Content Type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        header('Content-Type: ' . finfo_file($finfo, $filename));
        finfo_close($finfo);
        
        //Use Content-Disposition: attachment to specify the filename
        header('Content-Disposition: inline; name="'.basename($filename).'"; filename="'.basename($filename).'";');
        
        //No cache
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
    
        //Define file size
        header('Content-Length: ' . filesize($filename));
    
        ob_clean();
        flush();
        readfile($filename);
        exit;
    } else {
        header("Location: /");
        die();
    }
?>