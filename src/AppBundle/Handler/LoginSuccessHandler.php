<?php

namespace AppBundle\Handler;

use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Doctrine\Bundle\DoctrineBundle\Registry as Doctrine;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Router;
use AppBundle\Entity\User;


class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{

    protected $router;
    protected $authorizationChecker;
    protected $doctrine;


    public function __construct(AuthorizationChecker $authorizationChecker, Doctrine $doctrine, Router $router)
    {
        $this->router = $router;
        $this->authorizationChecker = $authorizationChecker;
        $this->doctrine = $doctrine;
    }


    /**
     * @param Request $request
     * @param TokenInterface $token
     * @return RedirectResponse
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        $em = $this->doctrine->getManager();
        $repository = $em->getRepository('AppBundle:User');
        $user = $repository->findOneByUsername($token->getUsername());
        if($user instanceof User){
            // update user last connection
            $user->setConnectedAt(new \DateTime());
            $em->persist($user);
            $em->flush();

            // switch to user language
            $_locale = $request->getLocale();
            if($user->getLocale() && $user->getLocale() != $_locale){
                $request->setLocale($user->getLocale());
                $this->router->getContext()->setParameter('_locale', $user->getLocale());
                $request->getSession()->set('_locale', $user->getLocale());
            }
        }

        if ($this->authorizationChecker->isGranted('ROLE_ADMIN')) {
            $response = new RedirectResponse($this->router->generate('admin_home_route'));
        } else {
            $response = new RedirectResponse($this->router->generate('home_route'));
        }

        return $response;
    }

}