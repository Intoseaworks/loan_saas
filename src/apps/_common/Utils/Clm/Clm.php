<?php

namespace Common\Utils\Clm;

use Common\Models\Order\Order;
use Common\Models\Order\OrderDetail;
use Common\Models\User\UserInfo;
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Common\Utils\DingDing\DingHelper;

class Clm {

    const GET_CUSTOMER_AMOUNT = "/level/getCustomerAmount";
    const GET_CUSTOMER_LEVEL = "/level/getCustomerLevel";

    private $_config = [
        "host" => "120.79.72.2",
        "port" => "5672",
        "user" => "upeso",
        "password" => "upeso@123",
        "vhost" => "/upeso_dev",
        "exchange" => "SLS_TO_APP_SERVER_EXCHANGE",
        "routingKey" => "000000000005.CONTRACT_STATUS_CHANGE",
        "org" => "000000000005",
        "api_domain" => "http://119.23.228.125:8084",
    ];
    private $_order;

    public function getCustomerAmount($order) {
        $this->_order = $order;
        $data = [
            "socialStatus" => $order->user->userWork->employment_type ?? "",
            "applyId" => $order->order_no ??'',
            "idcard" => $order->userInfo->aadhaar_card_no ?? "",
            "industryCode" => $order->user->userWork->profession ?? "",
            "birth" => date("Y-m-d", strtotime($order->userInfo->birthday ?? "")),
            "gender" => $order->userInfo->gender ?? "",
            "onlinePay" => "Y",
            "isContinuedLoan" => "N",
            "educationCode" => $order->userInfo->education_level ?? "",
            "howLongStaying" => $order->userInfo->live_length ?? "",
            "loanPurpose" => $order ? (new OrderDetail)->getLoanReason($order) : "",
            "maritalStatus" => $order->userInfo->marital_status ?? "",
            "contactRelationCode" => "",
            "transactionCode" => isset($order->tradeLogRemitSuccess) ? $order->tradeLogRemitSuccess->trade_platform_no : '',
            "whitelist" => "N",
            "phone" => $order->user->telephone ?? "",
            "name" => $order->user->fullname ?? "",
            "inputChannel" => $order->user->channel_id ?? "",
            "org" => $this->_config['org'],
        ];
        return $this->send($data, $this::GET_CUSTOMER_AMOUNT);
    }

    public function getCustomerLevel(UserInfo $userInfo) {
        $data = [
            "idcard" => $userInfo->aadhaar_card_no,
            "org" => $this->_config['org'],
        ];
        return $this->send($data, $this::GET_CUSTOMER_LEVEL);
    }

    public function addCustomer(Order $order) {
        $this->_order = $order;
        $data = [
            "socialStatus" => $order->user->userWork->employment_type,
            "applyId" => $order->order_no,
            "idcard" => $order->userInfo->aadhaar_card_no,
            "industryCode" => $order->user->userWork->profession,
            "birth" => $order->userInfo->birthday,
            "gender" => $order->userInfo->gender,
            "onlinePay" => "Y",
            "isContinuedLoan" => "N",
            "educationCode" => $order->userInfo->education_level,
            "howLongStaying" => $order->userInfo->live_length,
            "loanPurpose" => (new OrderDetail)->getLoanReason($order),
            "maritalStatus" => $order->userInfo->marital_status,
            "contactRelationCode" => "",
            "transactionCode" => $order->tradeLogRemitSuccess ? $order->tradeLogRemitSuccess->trade_platform_no : '',
            "whitelist" => "N",
            "phone" => $order->user->telephone,
            "name" => $order->user->fullname,
            "inputChannel" => $order->user->channel_id,
            "org" => $order->merchant_id,
        ];
        return $this->sendQueue($data);
    }

    public function pushApproveStatus(Order $order) {
        $data = [
            "org" => $this->_config['org'],
            "approveNo" => $order->order_no,
            "idCard" => $order->userInfo->aadhaar_card_no,
            "approveCode" => "",
            "approveAmount" => "",
            "loanTerms" => "",
            "rejectReason" => "",
            "rejectCode" => "",
            "whitelist" => "",
            "name" => "",
        ];
    }

    public function pushContract() {
        
    }

    public function sendQueue($data) {
        try {
            $data = json_encode($data);
            $connection = new AMQPConnection($this->_config['host'], $this->_config['port'], $this->_config['user'], $this->_config['password'], $this->_config['vhost']);
            $channel = $connection->channel();

            $msg = new AMQPMessage($data, ['content_type' => 'text/plain', 'delivery_mode' => 2]); //生成消息

            $channel->basic_publish($msg, $this->_config['exchange'], $this->_config['routingKey']); //推送消息到某个交换机
            $channel->close();
            $connection->close();
            return true;
        } catch (Exception $e) {
            DingHelper::notice($data, '发送CLM队列失败', DingHelper::AT_CXS);
            return false;
        }
    }

    public function send($data, $dir) {
        $curl = curl_init();

        $params = http_build_query($data);
        $this->_config['api_domain'] . $dir . "?" . $params;
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->_config['api_domain'] . $dir . "?" . $params,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $res = json_decode($response, true);
        return $res['data'];
    }

}
