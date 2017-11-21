<?php

namespace AppBundle\Handler;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;
use Doctrine\Bundle\DoctrineBundle\Registry as Doctrine;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

class LogoutSuccessHandler implements LogoutSuccessHandlerInterface
{

    protected $doctrine;
    protected $router;
    protected $session;

    public function __construct(Doctrine $doctrine, Router $router, Session $session)
    {
        $this->router = $router;
        $this->doctrine = $doctrine;
        $this->session = $session;
    }

    public function onLogoutSuccess(Request $request)
    {
        // redirect the user to where they were before the login process begun.
        $referer_url = $request->headers->get('referer');
        if($referer_url){
            $response = new RedirectResponse($referer_url);
        } else {
            $response = new RedirectResponse('/');
        }

        return $response;
    }

}