<?php

namespace App\Controller;

use App\Entity\Chambre;
use App\Entity\Client;
use App\Entity\Images;
use App\Entity\Reservation;
use App\Entity\User;
use App\Form\ChambreFormulaireType;
use App\Form\ClientFormulaireType;
use App\Form\RrvationType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class ChambreController extends AbstractController
{
    #[Route('/dash/Chambres', name: 'Liste_chambresss')]
    public function getChambres(): Response
    {

        $em=$this->getDoctrine()->getManager();
        $Liste=$em->getRepository(Chambre::class)->findAll();

        return $this->render('dashboard/listeChambres.html.twig', [
            "ListeChambre" => $Liste
        ]);

    }
    #[Route('/chambre', name: 'chambre')]
    public function index(): Response
    {
        return $this->render('chambre/index.html.twig.twig', [
            'controller_name' => 'ChambreController',
        ]);
    }
    #[Route('/', name: 'home')]
    public function home(): Response
    {
        return $this->render(view: 'index.html.twig');
    }
    #[Route('/contact', name: 'contact')]
    public function contact(): Response
    {
        return $this->render(view: 'contact-page.html.twig');
    }
    #[Route('/room', name: 'room')]
    public function room(): Response
    {
        return $this->render(view: 'rooms-and-suites.html.twig');
    }
    #[Route('/faci', name: 'facilities')]
    public function faci(): Response
    {
        return $this->render(view: 'facilities.html.twig');
    }
    #[Route('/dash/AddRoom', name: 'add_room')]

    public function addChambre(Request $request,SluggerInterface $slugger): \Symfony\Component\HttpFoundation\Response
    {

        $chambre=new Chambre();
        $form=$this->createForm(ChambreFormulaireType::class,$chambre);
        $form->handleRequest($request);

        if ($form->isSubmitted() and $form->isValid()){
            $images = $form->get('photos')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($images) {
                foreach($images as $image){
                $originalFilename = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$image->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $image->move(
                        $this->getParameter('client_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {

                }
                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $img=new Images();
                $img->setPath($newFilename);
                $chambre->addImage($img);}
            }
            $em=$this->getDoctrine()->getManager();
            $em->persist($chambre);
            $em->flush();
            return $this->redirectToRoute('add_room');

        }
        return $this->render('dashboard/AjoutChambre.html.twig', [
            "formChambre" => $form->createView()
        ]);
    }

    #[Route('/dash/roomEdit/{id}', name: 'edit_room')]

    public function EditChambre(Request $request,SluggerInterface $slugger,$id): \Symfony\Component\HttpFoundation\Response
    {
        $em=$this->getDoctrine()->getManager();
        $chambre=$em->getRepository(Chambre::class)->find($id);
        $form=$this->createForm(ChambreFormulaireType::class,$chambre);
        $form->handleRequest($request);

        if ($form->isSubmitted() and $form->isValid()){
            $images = $form->get('photos')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($images) {
                foreach($images as $image){
                    $originalFilename = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
                    // this is needed to safely include the file name as part of the URL
                    $safeFilename = $slugger->slug($originalFilename);
                    $newFilename = $safeFilename.'-'.uniqid().'.'.$image->guessExtension();

                    // Move the file to the directory where brochures are stored
                    try {
                        $image->move(
                            $this->getParameter('client_directory'),
                            $newFilename
                        );
                    } catch (FileException $e) {

                    }
                    // updates the 'brochureFilename' property to store the PDF file name
                    // instead of its contents
                    $img=new Images();
                    $img->setPath($newFilename);
                    $chambre->addImage($img);}
            }

            $em->persist($chambre);
            $em->flush();
            return $this->redirectToRoute('add_room');

        }
        return $this->render('dashboard/AjoutChambre.html.twig', [
            "formChambre" => $form->createView()
        ]);
    }


    #[Route('/dash/Chambre/{id}', name: 'chambreById')]
    public function getChambreById($id): Response
    {

        $em=$this->getDoctrine()->getManager();
        $Liste=$em->getRepository(Chambre::class)->find($id);

        return $this->render('dashboard/SingleRoom.html.twig', [
            "ListeChambre" => $Liste
        ]);

    }


}
