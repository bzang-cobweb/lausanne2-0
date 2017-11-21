<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Model;
use AppBundle\Entity\Player;
use AppBundle\Entity\Team;
use AppBundle\Entity\User;
use AppBundle\Form\Type\ChangePasswordType;
use AppBundle\Form\Type\ProfileType;
use AppBundle\Form\Type\SearchWordType;
use AppBundle\Lib\DataTable;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use AppBundle\Form\Type\PlayerType;
use Symfony\Component\Form\Form;

class AdminProfileController extends AdminController
{

    protected $page = 'profile';


    /**
     * @Route("/admin/my-account/", name="admin_profile_route")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function indexAction(Request $request)
    {
        $this->action = 'index';

        $form = $this->createForm(ChangePasswordType::class);
        if ($request->isMethod('POST')) {
            $this->passwordAction($request, $form, $this->getUser());
        }

        return $this->render('profile/index.html.twig', [
            'page_route' => 'admin_profile_route',
            'title' => $this->get('translator')->trans('label.my_account'),
            'form_password' => $form->createView(),
        ]);
    }

    /**
     * Edit profile
     *
     * @Route("/admin/my-account/edit/", name="admin_edit_profile_route")
     * @Method({"GET","POST"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function editAction(Request $request)
    {
        $profile = $this->getUser();
        if($profile instanceof User) {
            $cancel = $this->generateUrl('admin_profile_route');

            $form = $this->createForm(ProfileType::class, $profile);
            if ($request->isMethod('POST')) {
                try {
                    $response = $this->submitModelForm($request, $profile, $form);
                    if ($response == 'save') {
                        return $this->redirect($cancel);
                    }
                } catch (UniqueConstraintViolationException $e) {
                    $form->get('email')->addError(
                        new FormError($this->get('translator')->trans('error.duplicated'))
                    );
                } catch (\Exception $e) {
                    $this->get('session')->getFlashBag()
                        ->set('error', $this->get('translator')->trans('error.unknown'));
                }
            }

            $parameters = [
                'form_model' => $form->createView(),
                'title' => $this->get('translator')->trans('label.my_account'),
                'cancel_url' => $cancel
            ];

            return $this->render('profile/edit.html.twig', $parameters);
        }

        $this->get('session')->getFlashBag()->set('error', $this->get('translator')->trans('error.not_found'));
        return $this->redirect($this->generateUrl('admin_profile_route'));
    }

    /**
     * Change password profile
     */
    public function passwordAction(Request $request, Form $form, User $profile)
    {

        try {
            $form->handleRequest($request);
            if($form->isSubmitted()) {
                if ($form->isValid()) {
                    $password = $form->get('password')->getData();
                    $repeat = $form->get('repeat_password')->getData();
                    if($password == $repeat){
                        $factory = $this->get('security.encoder_factory');
                        $encoder = $factory->getEncoder($profile);
                        $password = $encoder->encodePassword($password, $profile->getSalt());
                        $profile->setPassword($password);
                        $em = $this->getDoctrine()->getManager();
                        $em->persist($profile);
                        $em->flush();
                        $this->get('session')->getFlashBag()->set('success', $this->get('translator')->trans('success.saved'));
                    } else {
                        $form->get('repeat_password')->addError(
                            new FormError($this->get('translator')->trans('error.repeat_password'))
                        );
                    }
                }
            }
        } catch (\Exception $e) {
            $this->get('session')->getFlashBag()
                ->set('error', $this->get('translator')->trans('error.unknown'));
        }
    }
}
