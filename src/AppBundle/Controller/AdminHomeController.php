<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
Use AppBundle\Entity\User;

class AdminHomeController extends AdminController
{
    /**
     * @Route("/admin/", name="admin_home_route")
     * @Method({"GET"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function indexAction(Request $request)
    {
        return $this->render('index.html.twig');
    }
}