<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 10:02
 */

namespace Api\Services\Feedback;

use Api\Models\Feedback\FeedbackFaq;
use Common\Services\Init\FaqServer;

class FeedbackFaqServer extends FaqServer
{
    /**
     * @param $param
     * @return mixed
     */
    public function getList($param)
    {
        $data = FeedbackFaq::model()->getList($param);
        return $data;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getOne($id)
    {
        $data = FeedbackFaq::model()->getOne($id);
        if (!$data) {
            return $this->outputException('记录不存在');
        }
        return $data->getText();
    }

}
