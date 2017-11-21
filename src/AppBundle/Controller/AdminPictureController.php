<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Championship;
use AppBundle\Entity\News;
use AppBundle\Entity\NewsPlayer;
use AppBundle\Entity\Picture;
use AppBundle\Entity\Player;
use AppBundle\Entity\Result;
use AppBundle\Entity\Team;
use AppBundle\Form\Type\NewsPlayerType;
use AppBundle\Form\Type\PictureType;
use AppBundle\Form\Type\UploadType;
use AppBundle\Lib\DataTable;
use AppBundle\Utility\EntityUtility;
use AppBundle\Utility\UploadUtility;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use AppBundle\Form\Type\NewsType;
use Symfony\Component\Form\Form;

class AdminPictureController extends AdminController
{

    protected $page = 'picture';


    /**
     *
     * @Route("/admin/pictures/{id}/delete/", name="admin_delete_picture_route", requirements={"id": "\d+"})
     * @Method({"GET"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function deleteAction(Request $request, $id)
    {
        if(!$request->isXMLHttpRequest()){
            return;
        }

        $picture = $id > 0 ? $this->getEntity($id, 'Picture') : null;
        if ($picture instanceof Picture) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($picture);
            $em->flush();

            // delete physical file
            $picture->deleteFile();

            return $this->json(
                [
                    'element' => 'picture-' . $id
                ]
            );
        } else {
            if($request->isXMLHttpRequest()){
                return $this->json(
                    [
                        'element' => 'picture-' . $id
                    ],
                    404
                );
            }
        }
    }
}
