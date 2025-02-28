<?php

namespace Api\Services\Feedback;


use Api\Models\Feedback\Feedback;
use Api\Services\BaseService;
use Common\Exceptions\ApiException;

class FeedbackService extends BaseService
{

    /**
     * @param $data
     * @return bool
     * @throws ApiException
     */
    public function add($data)
    {
        if (!Feedback::model()->add($data)) {
            throw new ApiException('保存失败');
        }
        return true;
    }

}