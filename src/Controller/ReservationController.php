<?php

namespace App\Controller;

use App\Entity\Chambre;
use App\Entity\Reservation;
use App\Entity\User;
use App\Form\RrvationType;
use App\Service\PdfService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ReservationController extends AbstractController
{
    #[Route('/reservation', name: 'reservation')]
    public function index(): Response
    {
        return $this->render('reservation/index.html.twig', [
            'controller_name' => 'ReservationController',
        ]);
    }
    #[Route('/dash/reserv', name: 'reservation')]
    public function getChambresdispo(Request $request): Response
    {



        $list=null;
        $form=$this->createForm(RrvationType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() and $form->isValid()){
            $date_deb = $form->get('date_deb')->getData();
            $date_fin=$form->get('date_fin')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded

            $em=$this->getDoctrine()->getManager();
            $query=$em->createQuery("SELECT c FROM App\Entity\Chambre c where c.id not in
             ( SELECT IDENTITY(r.chambre) FROM App\Entity\Reservation r where r.dateFin > '".$date_deb->format('Y-m-d').
                "' OR r.dateDebut BETWEEN '".$date_deb->format('Y-m-d')."'AND'".$date_fin->format('Y-m-d')."')");
            $list=$query->getResult();

            return $this->render('dashboard/reserver.html.twig', [
                "formSearch" => $form->createView(), "ListeChambre" => $list,"date_debut"=>$date_deb->format('Y-m-d'),"date_fin"=>$date_fin->format('Y-m-d')
            ]);
        }
        return $this->render('dashboard/reserver.html.twig', [
            "formSearch" => $form->createView(), "ListeChambre" => $list
        ]);

    }
    #[Route('/dash/reserver/{idC}/{idR}/{db}/{df}', name: 'addreserv')]
    public function addreserv($idC,$idR,$db,$df): Response
    {
        $reserv=new Reservation();
        $date_deb=new \DateTime($db);
        $date_fin=new \DateTime($df);
        $em=$this->getDoctrine()->getManager();
        $ch=$em->getRepository(Chambre::class)->find($idR);
        $u=$em->getRepository(User::class)->find($idC);
        $reserv->setChambre($ch);
        $reserv->setUser($u);
        $reserv->setDateDebut($date_deb);
        $reserv->setDateFin($date_fin);
        $reserv->setEtat("en cours");
        $reserv->setPrixJour($ch->getPrix());
        $reserv->setPrix($ch->getPrix()*($date_deb->diff($date_fin)->days+1));
        $em->persist($reserv);
        $em->flush();


        return $this->redirectToRoute("dashboard" );


    }
    #[Route('/dash/historeserv', name: 'historique')]
    public function getmyreserv(): Response
    {
        $id=$this->getUser()->getId();
        $em=$this->getDoctrine()->getManager();
        $query=$em->createQuery("SELECT r FROM App\Entity\Reservation r where IDENTITY(r.user)=".$id);
        $liste=$query->getResult();

        return $this->render('dashboard/Historiquereserv.html.twig', [
            "Listereserv" => $liste
        ]);

    }
    #[Route('/dash/getRecu/{id}', name: 'recu')]
    public function getrecuv($id,PdfService $pdf)
    {
        $user=$this->getUser();
        $em=$this->getDoctrine()->getManager();
        $reserv=$em->getRepository(Reservation::class)->find($id);

        $html= $this->render('dashboard/Recu.html.twig', [
            "client" => $user,"reserv"=>$reserv
        ]);
        $pdf->showPdfFile($html);

    }
    #[Route('/dash/listeDemande', name: 'liste_demande')]
    public function getReserv(): Response
    {
        $id=$this->getUser()->getId();
        $em=$this->getDoctrine()->getManager();
        $query=$em->createQuery("SELECT r FROM App\Entity\Reservation r where r.etat='en cours'");
        $liste=$query->getResult();

        return $this->render('dashboard/listedesreserv.html.twig', [
            "Listereserv" => $liste
        ]);

    }
    #[Route('/dash/Approuver/{id}', name: 'approuver')]
    public function approuver($id): Response
    {

        $em=$this->getDoctrine()->getManager();
        $reserv=$em->getRepository(Reservation::class)->find($id);

        $reserv->setEtat("Approuvée");
        $em->persist($reserv);
        $em->flush();


        return $this->redirectToRoute("liste_demande" );


    }
}
