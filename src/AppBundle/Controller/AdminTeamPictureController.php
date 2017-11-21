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

class AdminTeamPictureController extends AdminController
{

    protected $page = 'team';

    /**
     * Add team picture
     *
     * @Route("/admin/team/{id}/picture/", name="admin_add_team_picture_route",
     *     requirements={"id": "\d+"}
     * )
     * @Method({"GET","POST"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function addAction(Request $request, $id)
    {
        $team = $id > 0 ? $this->getModel($id, 'Team') : null;
        if($team instanceof Team) {
            $picture = new Picture();
            $form = $this->createForm(PictureType::class, $picture, [
                'action' => $this->generateUrl('admin_add_team_picture_route', [
                    'id' => $id
                ])
            ]);

            if ($request->isMethod('POST')) {
                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {
                    try {
                        $file = $picture->getFile();
                        if(!$file instanceof UploadedFile){
                            throw new Exception($this->get('translator')->trans('error.required'));
                        }

                        $path =  $this->getParameter('upload_path') . '/' . date('Y') . '/' . date('m') . '/' . date('d') . '/';
                        $relPath = $this->getParameter('web_path') . $path;
                        $name = time() . '-' . $id . '.' . $file->getClientOriginalExtension();

                        $picture->setName($name);
                        $picture->setPathname($path . $name);
                        $picture->setRealPathname($relPath . $name);
                        $picture->setExtension($file->getClientOriginalExtension());
                        $picture->setMimeType($file->getMimeType());
                        $picture->setTeam($team);

                        $file->move($relPath, $name);

                        $em = $this->getDoctrine()->getManager();
                        $em->persist($picture);
                        $em->flush();

                        $this->afterSave($picture);

                        $this->get('session')->getFlashBag()->set('success', $this->get('translator')->trans('success.saved'));
                        if($form->get('saveAndAdd')->isClicked()){
                            return $this->redirect($this->generateUrl('admin_add_team_picture_route', ['id' => $id]));
                        } else {
                            return $this->redirect($this->generateUrl('admin_detail_team_route', ['id' => $id, 'tab' => 4]));
                        }
                    } catch (\Exception $e) {
                        $form->get('file')->addError(new FormError($e->getMessage()));
                    }
                }
            }

            return $this->render('team/add_edit_picture.html.twig', [
                'form_picture' => $form->createView(),
                'form_title' => $this->get('translator')->trans('label.picture'),
                'form_messages' => [
                    sprintf($this->get('translator')->trans('file.accept_extensions'), implode(', ', Picture::$acceptExtensions)),
                    sprintf($this->get('translator')->trans('file.max_file_size'), Picture::getMaxFileSize())
                ],
                'picture' => $picture,
                'cancel_url' => $this->generateUrl('admin_detail_team_route', ['id' => $id, 'tab' => 4]),
                'title' => $this->get('translator')->trans('label.team') . ' ' . $id
            ]);
        }

        return $this->redirect($this->generateUrl('admin_detail_team_route', ['id' => $id]));
    }

    /**
     * Add news picture
     *
     * @Route("/admin/team-picture/{id}/", name="admin_edit_team_picture_route",
     *     requirements={"id": "\d+"}
     * )
     * @Method({"GET","POST"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function editAction(Request $request, $id)
    {
        $picture = $id > 0 ? $this->getModel($id, 'Picture') : null;
        if($picture instanceof Picture) {
            $form = $this->createForm(PictureType::class, $picture, [
                'action' => $this->generateUrl('admin_edit_team_picture_route', [
                    'id' => $id
                ])
            ]);

            $team = $picture->getTeam();
            if ($request->isMethod('POST')) {
                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {
                    try {
                        $file = $picture->getFile();
                        if($file instanceof UploadedFile){
                            $oldFilename = $picture->getRealPathname();

                            $path =  $this->getParameter('upload_path') . '/' . date('Y') . '/' . date('m') . '/' . date('d') . '/';
                            $relPath = $this->getParameter('web_path') . $path;
                            $name = time() . '-' . $id . '.' . $file->getClientOriginalExtension();

                            $picture->setName($name);
                            $picture->setPathname($path . $name);
                            $picture->setRealPathname($relPath . $name);
                            $picture->setExtension($file->getClientOriginalExtension());
                            $picture->setMimeType($file->getMimeType());
                            $file->move($relPath, $name);

                            // delete old physical file
                            if(file_exists($oldFilename)){
                                @unlink($oldFilename);
                            }
                        }

                        $picture->setUpdatedAt(new \DateTime());
                        $em = $this->getDoctrine()->getManager();
                        $em->persist($picture);
                        $em->flush();

                        $this->afterSave($picture);

                        $this->get('session')->getFlashBag()->set('success', $this->get('translator')->trans('success.saved'));
                        if($form->get('saveAndAdd')->isClicked()){
                            return $this->redirect($this->generateUrl('admin_add_team_picture_route', ['id' => $team->getId()]));
                        } else {
                            return $this->redirect($this->generateUrl('admin_detail_team_route', ['id' => $team->getId(), 'tab' => 4]));
                        }
                    } catch (\Exception $e) {
                        $form->get('file')->addError(new FormError($e->getMessage()));
                    }
                }
            }

            return $this->render('team/add_edit_picture.html.twig', [
                'form_picture' => $form->createView(),
                'form_title' => $this->get('translator')->trans('label.picture'),
                'form_messages' => [
                    sprintf($this->get('translator')->trans('file.accept_extensions'), implode(', ', Picture::$acceptExtensions)),
                    sprintf($this->get('translator')->trans('file.max_file_size'), Picture::getMaxFileSize())
                ],
                'picture' => $picture,
                'cancel_url' => $this->generateUrl('admin_detail_team_route', ['id' => $team->getId(), 'tab' => 4]),
                'title' => $this->get('translator')->trans('label.team') . ' ' . $team->getId()
            ]);
        }

        return $this->redirect($this->generateUrl('admin_team_route'));
    }

    /**
     * Process after saving the picture
     *
     * @param Picture $picture
     */
    public function afterSave(Picture $picture){
        /*
        if($picture->isCover() && $picture->getId() > 0 &&
            $picture->getNews() instanceof News && $picture->getNews()->getId() > 0){
            $this->getDoctrine()->getRepository('AppBundle:Picture')->uncover([
                'news_id' => $picture->getNews()->getId(),
                'id' => $picture->getId()
            ]);
        }*/
    }
}
