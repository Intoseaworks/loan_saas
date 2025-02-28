<?php

namespace Common\Jobs;

use Api\Services\Auth\AuthServer;
use Api\Services\Auth\Face\AuthFaceServer;
use Common\Models\User\User;
use Common\Utils\DingDing\DingHelper;
use Common\Utils\Email\EmailHelper;
use Common\Utils\MerchantHelper;

/**
 * Class SendEmailJob
 * @package App\Jobs
 * @author ChangHai Zhan
 */
class FaceComparisonJob extends Job
{
    public $queue = 'face-comparison';
    /**
     * The number of times the job may be attempted.
     * @var int
     */
    public $tries = 3;
    /**
     * @var User
     */
    public $user;

    /**
     * SendEmailJob constructor.
     * @param $content
     * @param null $title
     * @param null $mail
     * @param string|null $attachments
     */
    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * @throws \Exception
     */
    public function handle()
    {
        if (app()->environment() == 'local') {
            return;
        }
        try {
            MerchantHelper::setMerchantId($this->user->merchant_id);
            AuthFaceServer::server()->faceComparison($this->user);
        } catch (\Exception $e) {
            DingHelper::notice([
                'user_id' => $this->user->id ?? '',
                'e' => EmailHelper::warpException($e),
                'user' => $this->user,
            ], '人脸比对异常', DingHelper::AT_CXS);
        }
    }
}
