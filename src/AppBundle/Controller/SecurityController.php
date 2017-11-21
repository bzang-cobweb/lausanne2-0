<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Admin;
use AppBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class SecurityController extends FrontController
{
    protected $page = 'security';

    /**
     * @Route("/sign-in/", name="login_route")
     */
    public function indexAction(Request $request)
    {
        $authenticationUtils = $this->get('security.authentication_utils');

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render(
            'security/login.html.twig',
            [
                // last username entered by the user
                'last_username' => $lastUsername,
                'error'         => $error,
            ]
        );
    }


    /**
     * @Route("/login_check", name="login_check_route")
     */
    public function loginCheckAction(Request $request)
    {

    }

    /**
     * @Route("/sign-out", name="admin_logout_route"))
     * @Method({"GET"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function logoutAction(Request $request){

    }
}
