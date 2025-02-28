<?php
/**
 * Created by PhpStorm.
 * User: jinqianbao
 * Date: 2019/1/31
 * Time: 10:55
 */

namespace Common\Jobs;

use Admin\Models\Notice\Notice;
use Common\Models\Merchant\App;
use Common\Utils\Email\EmailHelper;
use Common\Utils\Push\Services\GooglePush;

class PushNoticeJob extends Job
{
    /**
     * The number of times the job may be attempted.
     * @var int
     */
    public $tries = 3;
    /**
     * @var string|null
     */

    public $notice;

    public function __construct(Notice $notice)
    {
        $this->notice = $notice;
    }

    public function handle()
    {
        $app = App::model()->getNormalAppById($this->notice->app_id);
        $topic = GooglePush::TOPIC_APP . $app->app_key;
        if ($app && GooglePush::sendMessageToGroup($topic, $this->notice->title, $this->notice->content, $app->google_server_key)) {
            $this->notice->status = Notice::STATUS_SENDED;
            return $this->notice->save();
        }
        EmailHelper::send(['id' => $this->notice->id, 'title' => $this->notice->title], '公告发送异常');
    }
}
