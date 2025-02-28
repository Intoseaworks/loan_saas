<?php

/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/2/11
 * Time: 9:53
 */

namespace Common\Services\OrderAgreement;

use Admin\Models\Order\Order;
use Common\Models\Order\OrderDetail;
use Admin\Services\BaseService;
use Common\Services\Partner\PartnerAccountServer;
use Common\Utils\Data\DateHelper;
use Mpdf\Mpdf;

/**
 * Class OrderAgreementServer
 * @package Common\Services\OrderAgreement
 * @phan-file-suppress PhanUndeclaredClassMethod, PhanUndeclaredTypeThrowsType, PhanUndeclaredClassProperty
 */
class SanctionLetterServer extends BaseService {

    public function generate($orderId,$toHtml = false) {
        $order = Order::model()->getOne($orderId);
        $data = static::buildData($order);
        $content = SanctionLetterTemplate::$content;
        //print_r($data);
        /** 遍历写入订单数据 */
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                continue;
            }
            $content = str_replace('{{' . $key . '}}', $value, $content);
        }
        $content = str_replace(PHP_EOL, '<br/>', $content);
        
        if($toHtml){
            return $content;
        }
        return $this->buildPdf($content);
    }

    /**
     * 构建协议数据
     * @param Order $order
     * @param $isNew
     * @return array
     * @throws \Exception
     */
    protected function buildData($order) {
        $partnerDetail = PartnerAccountServer::server()->partnerDetail();

        $order->setScenario(Order::SCENARIO_LIST)->getText();
        $order->signed_time = DateHelper::formatToDate($order->signed_time);
        $order->fullname = $order->user->fullname;
        $order->address = $order->userInfo->address;
        $order->loan_reason = (new OrderDetail)->getLoanReason($order);
        $order->total_interest_amount = $order->interestFee();
        $order->processing_fees = $order->getProcessingFee();
        $order->repayment = $order->lastRepaymentPlan->repay_amount ?? '---';
//        $order->order_no = $order->order_no;
//        $order->signed_time = $order->signed_time;
        return $order->toArray();
    }

    /**
     * 生成pdf文件
     * @param $content
     * @param bool $needUpload
     * @return array
     * @throws \Mpdf\MpdfException
     */
    public function buildPdf($content) {
        $mpdf = new Mpdf([
            'mode' => '+aCJK',
        ]);
        $mpdf->SetDisplayMode('fullpage');
        /** 解决中文乱码问题 */
        $mpdf->autoScriptToLang = true;
        $mpdf->autoLangToFont = true;
        $mpdf->useAdobeCJK = true;
        $mpdf->WriteHTML($content);
        return $mpdf->Output('tempabc.pdf',"S");

    }

}
