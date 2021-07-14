<?php

namespace App\Subscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Analysis;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class DataSubscriber implements EventSubscriberInterface
{
    private ParameterBagInterface $parameterBag;
    private EntityManagerInterface $entityManager;

    public function __construct(ParameterBagInterface $parameterBag, EntityManagerInterface $entityManager)
    {
        $this->parameterBag = $parameterBag;
        $this->entityManager = $entityManager;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['Convert', EventPriorities::PRE_SERIALIZE],
        ];
    }

    public function Convert(ViewEvent $event)
    {
        $method = $event->getRequest()->getMethod();
        $route = $event->getRequest()->attributes->get('_route');
        $resource = $event->getControllerResult();

        if ($resource instanceof Analysis) {
            $resource->setMethod($method);
            $resource->setRoute($route);
            $resource->setPathInfo($event->getRequest()->getPathInfo());
            $resource->setBasePath($event->getRequest()->getBasePath());
            $resource->setUri($event->getRequest()->getUri());
            $resource->setRequestUri($event->getRequest()->getRequestUri());
            $resource->setRemoteHost(implode(",", $_SERVER));
            $this->entityManager->persist($resource);
            $this->entityManager->flush();
        }
    }
}
