<?php

namespace Common\Utils\CallCenter;

use Common\Utils\Data\StringHelper;

class Freeswitchesl {

    const CONFIG = [
        "dev" => [
            1 => [
                "server_ip" => "47.241.37.146",
                "password" => "saas@123",
            ],
            2 => [
                "server_ip" => "47.241.37.146",
                "password" => "saas@123",
            ],
            3 => [
                "server_ip" => "47.241.37.146",
                "password" => "saas@123",
            ],
            4 => [
                "server_ip" => "47.241.37.146",
                "password" => "saas@123",
            ],
            6 => [
                "server_ip" => "47.241.37.146",
                "password" => "saas@123",
            ],
            7 => [
                "server_ip" => "47.241.37.146",
                "password" => "saas@123",
            ],
            8 => [
                "server_ip" => "47.241.37.146",
                "password" => "saas@123",
            ],
        ],
        "prod" => [
            1 => [
                "server_ip" => "47.241.37.146",
                "password" => "saas@123",
            ],
            2 => [
                "server_ip" => "47.241.37.146",
                "password" => "saas@123",
            ],
            3 => [
                "server_ip" => "47.241.37.146",
                "password" => "saas@123",
            ],
            4 => [
                "server_ip" => "47.241.37.146",
                "password" => "saas@123",
            ],
            6 => [
                "server_ip" => "47.241.37.146",
                "password" => "saas@123",
            ],
            7 => [
                "server_ip" => "47.241.37.146",
                "password" => "saas@123",
            ],
            8 => [
                "server_ip" => "47.241.37.146",
                "password" => "saas@123",
            ],
        ]
    ];

    # 测试分机号与商铺对应
    const TEST_CALL_MERCHANT_MAP = [
        "8885" => 1,
        "8886" => 2,
        "8887" => 3,
        "8888" => 4,
    ];

    const TEST_CALL_GATEWAY_MAP = [
        "8885" => "gw8885",
        "8886" => "gw8886",
        "8887" => "gw8887",
        "8888" => "gw8888",
        "8870" => "gw8870",
        "8871" => "gw8871",
        "8872" => "gw8872",
        "8873" => "gw8873",
        "8874" => "gw8874",
        "8875" => "gw8875",
        "8876" => "gw8876",
        "8877" => "gw8877",
        "8878" => "gw8878",
        "8879" => "gw8879",
        "8889" => "gw8889",
        "8890" => "gw8890",
    ];

    //对于人审审核结果为挂起（待电二审）的订单需要通过自动外呼进行拨打，打通后，添加标记
    const TEST_CALL_MERCHANT_MAP_APPROVE_POOL = [
        "8881" => 1,
        "8882" => 2,
        "8883" => 3,
        "8884" => 4,
        "8880" => 6,
        "8889" => 7,
        "8890" => 8,
    ];

    const TEST_CALL_GATEWAY_MAP_APPROVE_POOL = [
        "8881" => "gw8881",
        "8882" => "gw8882",
        "8883" => "gw8883",
        "8884" => "gw8884",
        "8880" => "gw8880",
        "8889" => "gw8889",
        "8890" => "gw8890",
    ];

    public $_server = "";
    public $_port = '5060';
    public $_env = 'test';

    public static function factory($env, $merchantId) {
        $freeswitch = new Freeswitchesl();
        $freeswitch->_env = $env;
        $ip = self::CONFIG[$env][$merchantId]['server_ip'];
        $password = self::CONFIG[$env][$merchantId]['password'];
        $connect = $freeswitch->connect($ip, "8021", $password);
        if ($connect) {
            return $freeswitch;
        } else {
            throw new \Exception("Cannot connect to freeswitch");
        }
    }

    public function callToNumber($extension, $telephone, $mp3Path='') {
        $uuid = StringHelper::generateUuid();
        if($this->_env != 'prod'){
            return $uuid;
        }
        if($mp3Path == ''){
            $mp3Path = "/tmp/m1.mp3";
            if(isset(self::TEST_CALL_MERCHANT_MAP[$extension])){
                $mp3Path = "/tmp/m".self::TEST_CALL_MERCHANT_MAP[$extension].".mp3";
            }
        }
        if (isset(self::TEST_CALL_GATEWAY_MAP[$extension])) {
            $command = "originate {origination_uuid=$uuid}sofia/gateway/".self::TEST_CALL_GATEWAY_MAP[$extension]."/0{$telephone} &endless_playback({$mp3Path})";
//            $command = "originate {origination_uuid=$uuid}sofia/gateway/".self::TEST_CALL_GATEWAY_MAP[$extension]."/0{$telephone} &endless_playback(/tmp/m".self::TEST_CALL_MERCHANT_MAP[$extension].".mp3)";
        } else {
            if (strlen($telephone) == 4) {
                $command = "originate {origination_uuid=$uuid,origination_caller_id_number={$telephone}}user/{$extension} &bridge({origination_caller_id_number=9*********9}user/{$telephone})";
            } else {
                $telephone = substr($telephone, -10);
                $command = "originate {origination_uuid={$uuid},origination_caller_id_number=0{$telephone}}user/{$extension} 0{$telephone}";
            }
        }
        #$command = "originate {origination_uuid=$uuid,origination_caller_id_number={$extension}}user/{$extension} 0{$telephone}";
        
//        echo $command.PHP_EOL;
        $res = $this->bgapi($command);
        if ($res == "executed") {
            return $uuid;
        } else {
            return false;
        }
    }

    public function callToNumberApprovePool($extension, $telephone) {
        $uuid = StringHelper::generateUuid();
        if($this->_env != 'prod'){
            return $uuid;
        }
        
        #$command = "originate {origination_uuid=$uuid,origination_caller_id_number={$extension}}user/{$extension} 0{$telephone}";
        if (isset(self::TEST_CALL_MERCHANT_MAP_APPROVE_POOL[$extension])) {
            $command = "originate {origination_uuid=$uuid}sofia/gateway/".self::TEST_CALL_GATEWAY_MAP_APPROVE_POOL[$extension]."/0{$telephone} &endless_playback(/tmp/a".self::TEST_CALL_MERCHANT_MAP_APPROVE_POOL[$extension].".mp3)";
        } else {
            if (strlen($telephone) == 4) {
                $command = "originate {origination_uuid=$uuid,origination_caller_id_number={$telephone}}user/{$extension} &bridge({origination_caller_id_number=9*********9}user/{$telephone})";
            } else {
                $telephone = substr($telephone, -10);
                //$command = "originate {origination_uuid=$uuid,origination_caller_id_number={$telephone}}user/{$extension} &bridge({origination_caller_id_number=9*********9}sofia/internal/{$telephone}@{$this->_server}:5060)";
                $command = "originate {origination_uuid={$uuid},origination_caller_id_number=0{$telephone}}user/{$extension} 0{$telephone}";
            }
        }
        $res = $this->bgapi($command);
        if ($res == "executed") {
            return $uuid;
        } else {
            return false;
        }
    }

    public function __construct() {
        $this->socket = "";
        $this->sorts = "";
        $this->length = 1024;
    }

    public function eliminate($parameter) {
        $array = array(" ", "　", "\t", "\n", "\r");
        return str_replace($array, '', $parameter);
    }

    public function eliminateLine($parameter) {
        return str_replace("\n\n", "\n", $parameter);
    }

    public function typeClear($response) {
        $commenType = array("Content-Type: text/event-xml\n", "Content-Type: text/event-plain\n", "Content-Type: text/event-json\n");
        return str_replace($commenType, '', $response);
    }

    public function connect($host, $port, $password) {
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        $this->_server = $host;
        $connection = socket_connect($this->socket, $host, $port);
        $connect = false;
        $error = "";
        while ($socket_info = @socket_read($this->socket, 1024, PHP_NORMAL_READ)) {
            $eliminate_socket_info = $this->eliminate($socket_info);
            if ($eliminate_socket_info == "Content-Type:auth/request") {
                socket_write($this->socket, "auth " . $password . "\r\n\r\n");
            } elseif ($eliminate_socket_info == "") {
                continue;
            } elseif ($eliminate_socket_info == "Content-Type:command/reply") {
                continue;
            } elseif ($eliminate_socket_info == "Reply-Text:+OKaccepted") {
                $connect = true;
                break;
            } else {
                $error .= $eliminate_socket_info . "\r\n";
            }
        }
        if (!$connect) {
            echo $error;
        }
        return $connect;
    }

    public function api($api, $args = "") {
        if ($this->socket) {
            socket_write($this->socket, "api " . $api . " " . $args . "\r\n\r\n");
        }
        $response = $this->recvEvent("common");
        return $response;
    }

    public function bgapi($api, $args = "", $custom_job_uuid = "") {
        if ($this->socket) {
            socket_write($this->socket, "bgapi " . $api . " " . $args . " " . $custom_job_uuid . "\r\n\r\n");
        }
        return "executed";
    }

    public function execute($app, $args, $uuid) {
        if ($this->socket) {
            $str = "sendmsg " . $uuid . "\ncall-command: execute\nexecute-app-name: " . $app . "\nexecute-app-arg: " . $args . "\n\n";
            socket_write($this->socket, $str);
        }
        $response = $this->recvEvent("common");
        return $response;
    }

    public function executeAsync($app, $args, $uuid) {
        if ($this->socket) {
            $str = "sendmsg " . $uuid . "\ncall-command: executeAsync\nexecute-app-name: " . $app . "\nexecute-app-arg: " . $args . "\n\n";
            socket_write($this->socket, $str);
        }
        return "executed";
    }

    public function sendmsg($uuid) {
        if ($this->socket) {
            socket_write($this->socket, "sendmsg " . $uuid . "\r\n\r\n");
        }
        return "executed";
    }

    public function events($sorts, $args) {
        $this->sorts = $sorts;
        if ($sorts == "json") {
            $sorts = "xml";
        }
        if ($this->socket) {
            socket_write($this->socket, "event " . $sorts . " " . $args . "\r\n\r\n");
        }
        return true;
    }

    public function getHeader($response, $args) {
        $serialize = $this->serialize($response, "json");
        $serializearray = json_decode($serialize);
        try {
            return $serializearray->$args;
        } catch (Exception $e) {
            return "";
        }
    }

    public function recvEvent($type = "event") {
        $response = '';
        $length = 0;
        $x = 0;
        while ($socket_info = @socket_read($this->socket, 1024, PHP_NORMAL_READ)) {
            $x++;
            usleep(100);
            if ($length > 0) {
                $response .= $socket_info;
            }
            if ($length == 0 && strpos($socket_info, 'Content-Length:') !== false) {
                $lengtharray = explode("Content-Length:", $socket_info);
                if ($type == "event") {
                    $length = (int) $lengtharray[1] + 30;
                } else {
                    $length = (int) $lengtharray[1];
                }
            }

            if ($length > 0 && strlen($response) >= $length) {
                break;
            }

            if ($x > 10000)
                break;
        }


        if ($this->sorts == "json" && $type == "event") {
            $response = $this->typeClear($response);
            $responsedata = simplexml_load_string($response);
            $response = [];
            foreach ($responsedata->headers->children() as $key => $value) {
                $response[(string) $key] = (string) $value;
            }
            return json_encode($response);
        } else {
            $response = $this->eliminateLine($response);
        }
        return $response;
    }

    public function serialize($response, $type) {
        $response = $this->typeClear($response);
        if ($this->sorts == $type)
            return $response;
        if ($this->sorts == "json") {
            $responsedata = json_decode($response);
            if ($type == "plain") {
                $response = "";
                foreach ($responsedata as $key => $value) {
                    $responseline = $key . ": " . $value . "\r\n";
                    $response .= $responseline;
                }
            } else {
                $response = "<event>\r\n  <headers>\r\n";
                foreach ($responsedata as $key => $value) {
                    $responseline = "    <" . $key . ">" . $value . "</" . $key . ">" . "\r\n";
                    $response .= $responseline;
                }
                $response .= "  </headers>\r\n</event>";
            }
            return $response;
        } elseif ($this->sorts == "xml") {
            $responsedata = simplexml_load_string($response);
            if ($type == "plain") {
                $response = "";
                foreach ($responsedata->headers->children() as $key => $value) {
                    $responseline = (string) $key . ": " . (string) $value . "\r\n";
                    $response .= $responseline;
                }
                return $response;
            } else {
                $response = [];
                foreach ($responsedata->headers->children() as $key => $value) {
                    $response[(string) $key] = (string) $value;
                }
                return json_encode($response);
            }
        } else {
            $response = str_replace("\n", '","', $response);
            $response = str_replace(": ", '":"', $response);
            $response = substr($response, 0, -2);
            $response = '{"' . $response . '}';
            if ($type == "json")
                return $response;
            $responsedata = json_decode($response);
            $response = "<event>\r\n  <headers>\r\n";
            foreach ($responsedata as $key => $value) {
                $responseline = "    <" . $key . ">" . $value . "</" . $key . ">" . "\r\n";
                $response .= $responseline;
            }
            $response .= "  </headers>\r\n</event>";
            return $response;
        }
    }

    public function disconnect() {
        socket_close($this->socket);
    }

}
