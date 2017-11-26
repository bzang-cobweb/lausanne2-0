<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Model;
use AppBundle\Entity\Player;
use AppBundle\Entity\Setting;
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

class AdminSettingController extends AdminController
{

    protected $page = 'setting';


    /**
     * @Route("/admin/settings/", name="admin_setting_route")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function indexAction(Request $request)
    {
        $this->action = 'index';

        $rows = $this->getDoctrine()->getRepository('AppBundle:Setting')->findBy([]);
        $settings = [];
        foreach ($rows as $setting){
            if($setting instanceof Setting){
                $settings[$setting->getName()] = $setting;
            }
        }

        if ($request->isMethod('POST')) {
            $em = $this->getDoctrine()->getManager();
            foreach ($request->request->all() as $name => $value){
                if(isset($settings[$name])){
                    $setting = $settings[$name];
                    $setting->setValue($value);
                    $setting->setUpdatedAt(new \DateTime());
                    $em->persist($setting);
                    $em->flush();
                    $settings[$name] = $setting;
                }
            }
        }

        return $this->render('setting/index.html.twig', [
            'page_route' => 'admin_setting_route',
            'settings' => $settings,
            'title' => $this->get('translator')->trans('label.settings'),
        ]);
    }
}
