<?php

namespace App\Controller;

use App\Entity\Image;
use App\Form\ImageType;
use App\Repository\ImageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/image")
 */
class ImageController extends Controller
{
    /**
     * @Route("/", name="image_index", methods="GET")
     */
    public function index(ImageRepository $imageRepository): Response
    {
        return $this->render('image/index.html.twig', ['images' => $imageRepository->findAll()]);
    }

    /**
     * @Route("/new", name="image_new")
     */
    public function new(Request $request): Response
    {
        $image = new Image();
        $form = $this->createForm(ImageType::class, $image);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {


            /** @var Symfony\Component\HttpFoundation\File\UploadedFile $file */
            $file = $form->get('imagefield')->getData();
    
            $name = $this->generateUniqueFileName().'.'.$file->guessExtension();

            // moves the file to the directory where brochures are stored
            $file->move(
                $this->getParameter('image_directory'),
                $name
            );

            // updates the 'brochure' property to store the PDF file name
            // instead of its contents
            $image->setImageField($name);
            // dump($name);
            // dump($file);
            dump($image);
            return $this->redirect($this->generateUrl('image_index'));
            // // return $this->redirectToRoute('image_index');
            $em = $this->getDoctrine()->getManager();
            $em->persist($image);
            $em->flush();
        
        
        }

        return $this->render('image/new.html.twig', [
         
            
            'form' => $form->createView(),
        ]);
    }

    /**
    * @return string
    */
    private function generateUniqueFileName()
    {
        // md5() reduces the similarity of the file names generated by
        // uniqid(), which is based on timestamps
        return md5(uniqid());
    }

    /**
     * @Route("/{id}", name="image_show", methods="GET")
     */
    public function show(Image $image): Response
    {
        return $this->render('image/show.html.twig', ['image' => $image]);
    }

    /**
     * @Route("/{id}/edit", name="image_edit", methods="GET|POST")
     */
    public function edit(Request $request, Image $image): Response
    {
        $form = $this->createForm(ImageType::class, $image);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('image_edit', ['id' => $image->getId()]);
        }

        return $this->render('image/edit.html.twig', [
            'image' => $image,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="image_delete", methods="DELETE")
     */
    public function delete(Request $request, Image $image): Response
    {
        if ($this->isCsrfTokenValid('delete'.$image->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($image);
            $em->flush();
        }

        return $this->redirectToRoute('image_index');
    }
}
