<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Championship;
use AppBundle\Entity\Match;
use AppBundle\Entity\MatchPlayer;
use AppBundle\Entity\Picture;
use AppBundle\Entity\Player;
use AppBundle\Entity\Result;
use AppBundle\Entity\Team;
use AppBundle\Form\Type\MatchPlayerType;
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
use AppBundle\Form\Type\MatchType;
use Symfony\Component\Form\Form;

class AdminMatchPictureController extends AdminController
{

    protected $page = 'match';

    /**
     * Add match picture
     *
     * @Route("/admin/matches/{id}/picture/", name="admin_add_match_picture_route",
     *     requirements={"id": "\d+"}
     * )
     * @Method({"GET","POST"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function addAction(Request $request, $id)
    {
        $match = $id > 0 ? $this->getEntity($id, 'Match') : null;
        if($match instanceof Match) {
            $picture = new Picture();
            $form = $this->createForm(PictureType::class, $picture, [
                'action' => $this->generateUrl('admin_add_match_picture_route', [
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
                        $picture->setMatch($match);

                        $file->move($relPath, $name);

                        $em = $this->getDoctrine()->getManager();
                        $em->persist($picture);
                        $em->flush();

                        $this->get('session')->getFlashBag()->set('success', $this->get('translator')->trans('success.saved'));
                        if($form->get('saveAndAdd')->isClicked()){
                            return $this->redirect($this->generateUrl('admin_add_match_picture_route', ['id' => $id]));
                        } else {
                            return $this->redirect($this->generateUrl('admin_detail_match_route', ['id' => $id, 'tab' => 2]));
                        }
                    } catch (\Exception $e) {
                        $form->get('file')->addError(new FormError($e->getMessage()));
                    }
                }
            }

            return $this->render('match/add_edit_picture.html.twig', [
                'form_picture' => $form->createView(),
                'form_title' => $this->get('translator')->trans('label.picture'),
                'form_messages' => [
                    sprintf($this->get('translator')->trans('file.accept_extensions'), implode(', ', Picture::$acceptExtensions)),
                    sprintf($this->get('translator')->trans('file.max_file_size'), Picture::getMaxFileSize())
                ],
                'picture' => $picture,
                'cancel_url' => $this->generateUrl('admin_detail_match_route', ['id' => $id, 'tab' => 2]),
                'title' => $this->get('translator')->trans('match.match') . ' ' . $id
            ]);
        }

        return $this->redirect($this->generateUrl('admin_detail_match_route', ['id' => $id]));
    }

    /**
     * Add match picture
     *
     * @Route("/admin/match-picture/{id}/", name="admin_edit_match_picture_route",
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
                'action' => $this->generateUrl('admin_edit_match_picture_route', [
                    'id' => $id
                ])
            ]);

            $match = $picture->getMatch();
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

                        $this->get('session')->getFlashBag()->set('success', $this->get('translator')->trans('success.saved'));
                        if($form->get('saveAndAdd')->isClicked()){
                            return $this->redirect($this->generateUrl('admin_add_match_picture_route', ['id' => $match->getId()]));
                        } else {
                            return $this->redirect($this->generateUrl('admin_detail_match_route', ['id' => $match->getId(), 'tab' => 2]));
                        }
                    } catch (\Exception $e) {
                        $form->get('file')->addError(new FormError($e->getMessage()));
                    }
                }
            }

            return $this->render('match/add_edit_picture.html.twig', [
                'form_picture' => $form->createView(),
                'form_title' => $this->get('translator')->trans('label.picture'),
                'form_messages' => [
                    sprintf($this->get('translator')->trans('file.accept_extensions'), implode(', ', Picture::$acceptExtensions)),
                    sprintf($this->get('translator')->trans('file.max_file_size'), Picture::getMaxFileSize())
                ],
                'picture' => $picture,
                'cancel_url' => $this->generateUrl('admin_detail_match_route', ['id' => $match->getId(), 'tab' => 2]),
                'title' => $this->get('translator')->trans('match.match') . ' ' . $match->getId()
            ]);
        }

        return $this->redirect($this->generateUrl('admin_match_route'));
    }
}
