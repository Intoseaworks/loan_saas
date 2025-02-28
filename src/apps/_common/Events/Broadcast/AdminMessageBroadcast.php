<?php

namespace Common\Events\Broadcast;

use Common\Models\Message\Message;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class AdminMessageBroadcast extends Broadcaster implements ShouldBroadcast
{
    // 默认事件名
    protected $event = 'warning';

    public $description;

    public $title;

    public $type;

    public $relevance;

    // notification持续时间s，0为取消自动关闭
    public $duration = 0;

    protected static $CHANNEL = 'admin-message';

    /**
     * Create a new event instance.
     * @param $merchantId
     * @param string $relevance
     * @param string $title
     * @param string $content
     * @param int $type
     */
    public function __construct($merchantId, $relevance, string $title, string $content, int $type = Message::TYPE_WARNING)
    {
        $this->receiver = $merchantId;
        $this->relevance = $relevance;
        $this->title = $title;
        $this->description = $content;
        $this->type = $type;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return $this->privateChannel($this->receiver);
    }

//    指定广播数据，若不定义此方法，则将取当前类中的public属性当广播数据
//    public function broadcastWith()
//    {
//        return ['id' => $this->user->id];
//    }
}
