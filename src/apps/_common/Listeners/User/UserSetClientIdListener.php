<?php

namespace Common\Listeners\User;

use Common\Events\User\UserSetClientIdEvent;

class UserSetClientIdListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
    }

    public function handle(UserSetClientIdEvent $event)
    {
        $user = $event->getUser();
        $clientId = $event->getClientId();

        if(in_array($clientId, array_keys($user::CLIENT)) && $clientId != $user::CLIENT_H5){
           $user->updateClientId($user->id, $clientId);
        }

        return $user;
    }
}
