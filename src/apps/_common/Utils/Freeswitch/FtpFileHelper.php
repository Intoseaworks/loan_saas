<?php

namespace Common\Utils\Freeswitch;

use Common\Utils\Helper;
use Common\Models\Call\CallFile;

class FtpFileHelper {

    use Helper;

    const SERVER_IP = '47.241.37.146';
    const SERVER_USER = "ftptest";
    const SERVER_PWD = "Qaz125346";

    public function ftp() {
        $conn = ftp_connect(self::SERVER_IP) or die("Could not connect");
        ftp_login($conn, self::SERVER_USER, self::SERVER_PWD);

        ftp_set_option($conn, FTP_USEPASVADDRESS, false);
        ftp_pasv($conn, true);
        $allData = ftp_nlist($conn, "");
        echo 'ok';
        //2021-06-21-11-11-12_639451524741_1021.wav
        if (!$allData) {
            die("No data");
        }
        rsort($allData);
        foreach ($allData as $key => $item) {
            $num = explode('_', str_replace([".wav", ".mp3"], "", $item));
            if (isset($num[1])) {
                if(CallFile::model()->where("filename", $item)->exists()){
                    echo $item;
                    echo '已存在';
                    break;
                }
                $file = [
                    "size" => ftp_size($conn, $item),
                    "filename" => $item,
                    "call_time" => substr($item, 0, 4) . "-" . substr($item, 5, 2) . "-" . substr($item, 8, 2) . " " . substr($item, 11, 2) . ":" . substr($item, 14, 2) . ":" . substr($item, 17, 2),
                    "telephone" => substr($num[1], -10, 10),
                    "ext" => $num[2],
                ];
                CallFile::model()->updateOrCreateModel($file, ["filename" => $file['filename']]);
                echo '-';
            }else{
                echo $item;
            }
        }
        ftp_close($conn);
    }

}
