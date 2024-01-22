<?php 

namespace App\Events;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;

class JwtCreatedSubscriber{

    public function updateJwtData(JWTCreatedEvent $event)
    {
        // récup l'utilisateur (pour avoir firstName et le lastName)
        $user = $event->getUser(); // permet de récup l'utilisateur

        $data = $event->getData(); // réup un tableau qui contient les données de base sur l'utilisateur dans le JWT

        $data['firstName'] = $user->getFirstName();
        $data['lastName'] = $user->getLastName(); 

        $event->setData($data); // on repasse le tableau data avec les nouvelles données

    }
}