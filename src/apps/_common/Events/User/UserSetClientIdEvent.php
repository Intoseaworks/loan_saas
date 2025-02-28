<?php

namespace Common\Events\User;

use Common\Events\Event;
use Common\Models\User\User;

class UserSetClientIdEvent extends Event
{
    protected $user;

    protected $clientId;

    /**
     * Create a new event instance.
     * @param $user
     * @param $clientId
     */
    public function __construct($user, $clientId)
    {
        $this->clientId = $clientId;

        $this->user = $user;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    public function getClientId()
    {
        return $this->clientId;
    }

}
