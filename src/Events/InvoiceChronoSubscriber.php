<?php

namespace App\Events;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Invoice;
use App\Repository\InvoiceRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class InvoiceChronoSubscriber implements EventSubscriberInterface
{

    public function __construct(private Security $security, private InvoiceRepository $repo)
    {}

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ["setChronoForInvoice", EventPriorities::PRE_VALIDATE]
        ];
    }

    public function setChronoForInvoice(ViewEvent $event)
    {
        $invoice = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if($invoice instanceof Invoice && $method === "POST")
        {
            $user = $this->security->getUser();
            // besoin d'une méthode pour trouver et donner le dernier chrono + 1
            $nextChrono = $this->repo->findNextChrono($user);
            $invoice->setChrono($nextChrono);

            if(empty($invoice->getSentAt()))
            {
                $invoice->setSentAt(new \DateTime());
            }
        }
    }



}