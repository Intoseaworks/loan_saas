<?php

namespace Common\Events\Broadcast;

use Common\Events\Event;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Support\Arr;

abstract class Broadcaster extends Event
{
    use InteractsWithSockets;

    /** @var string 默认事件名 */
    protected $event;

    /** @var array 接收者 */
    protected $receiver;

    protected static $CHANNEL = 'channel';

    public static function getChannelName($prefix = null)
    {
        return $prefix ? static::$CHANNEL . '.' . $prefix : static::$CHANNEL;
    }

    /**
     * 广播事件名，若不定义此方法，默认取当前类的全名
     * @return string
     */
    public function broadcastAs()
    {
        return $this->getEvent();
    }

    protected function privateChannel($prefix)
    {
        $prefixs = Arr::wrap($prefix);
        $channel = [];
        foreach ($prefixs as $prefix) {
            $channel[] = new PrivateChannel(self::getChannelName($prefix));
        }

        return $channel;
    }

    protected function presenceChannel($prefix)
    {
        return new PresenceChannel(self::getChannelName($prefix));
    }

    protected function channel()
    {
        return new Channel(self::getChannelName());
    }

    public function setEvent(string $event)
    {
        $this->event = $event;
    }

    public function getEvent()
    {
        return $this->event;
    }

    public function addReceiver($receiver)
    {
        $this->receiver = array_merge($this->receiver, Arr::wrap($receiver));
    }
}
