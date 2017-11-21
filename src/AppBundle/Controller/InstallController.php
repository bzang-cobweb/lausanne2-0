<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Language;
use AppBundle\Entity\Role;
use AppBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class InstallController extends FrontController
{

    /**
     * @Route("/install/", name="install_route")
     * @Method({"GET"})
     */
    public function indexAction(Request $request)
    {
        $token = $request->get('token', null);
        if(strcasecmp($token, 'fZaFYGZD9ztHirMCWmyzCAPNqre4kfXAQ') == 0) {
            $em = $this->getDoctrine()->getManager();

            /******************** languages - START ********************/
            $languages = [
                ['code' => 'en', 'name' => 'English'],
                ['code' => 'fr', 'name' => 'Français']
            ];

            foreach ($languages as $row) {
                $code = $row['code'];
                $language = $this->getDoctrine()->getRepository('AppBundle:Language')->findOneBy(
                    ['code' => $code]
                );
                if ($language instanceof Language) {
                    $language->hydrate($row);
                    $language->setUpdatedAt(new \DateTime());
                } else {
                    $language = new Language($row);
                }
                $em->persist($language);
            }
            $em->flush();
            /******************** languages - END ********************/


            /******************** roles - START ********************/
            $roles = ['ROLE_USER', 'ROLE_ADMIN'];

            foreach ($roles as $name) {
                $role = $this->getDoctrine()->getRepository('AppBundle:Role')->findOneBy(
                    ['name' => $name]
                );
                if ($role instanceof Role) {
                    $role->setUpdatedAt(new \DateTime());
                } else {
                    $role = new Role();
                    $role->setName($name);
                }
                $em->persist($role);
            }
            $em->flush();
            /******************** roles - END ********************/

            /******************** users - START ********************/
            $user = $this->getDoctrine()->getRepository('AppBundle:User')->findOneBy(
                ['email' => 'etienne.bertrand.zang@gmail.com']
            );

            if (!$user instanceof User) {
                $user = new User();

                $factory = $this->get('security.encoder_factory');
                $encoder = $factory->getEncoder($user);
                $password = $encoder->encodePassword('123456', $user->getSalt());

                $user->setEmail('etienne.bertrand.zang@gmail.com');
                $user->setUsername('etienne.bertrand.zang@gmail.com');
                $user->setPassword($password);
                $user->setLanguage($language);
                $user->addRole($role);
                $em->persist($user);
                $em->flush();
            }
            $em->clear();

            return $this->render('install.html.twig',
                [
                    'success' => $this->get('translator')->trans('success.install'),
                ]
            );
        } else {
            return $this->render('install.html.twig',
                [
                    'error' => $this->get('translator')->trans('error.bad_token'),
                ]
            );
        }
    }
}
