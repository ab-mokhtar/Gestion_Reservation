<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\User;
use App\Form\ClientFormulaireType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class ClientController extends AbstractController
{
    #[Route('/client', name: 'client')]
    public function index(): \Symfony\Component\HttpFoundation\Response
    {
        return $this->render('client/index.html.twig', [
            'controller_name' => 'ClientController',
        ]);
    }

    #[Route('/dash/client/liste', name: 'clients')]
    public function listeClients(): \Symfony\Component\HttpFoundation\Response
    {

        $em=$this->getDoctrine()->getManager();
        $ListeClients=$em->getRepository(Client::class)->findAll();

        return $this->render('client/index.html.twig', [
            "ListeClient" => $ListeClients
        ]);
    }
    #[Route('/dash/client/{id}', name: 'client_byId')]
    public function ClientById($id): \Symfony\Component\HttpFoundation\Response
    {

        $em=$this->getDoctrine()->getManager();
        $ListeClients=$em->getRepository(Client::class)->find($id);

        return $this->render('client/index2.html.twig', [
            "ListeClient" => $ListeClients
        ]);
    }
    #[Route('/dash/clientdel/{id}', name: 'delete_client')]
    public function ClientDelete($id): \Symfony\Component\HttpFoundation\RedirectResponse
    {


        $em=$this->getDoctrine()->getManager();
        $ListeClients=$em->getRepository(User::class)->find($id);
        if ($ListeClients!= null){
            $em->remove($ListeClients);
            $em->flush();
        }

        return $this->redirectToRoute('Liste_Clients');
    }

    #[Route('/dash/addClient', name: 'add_client')]
    public function addClient(Request $request,SluggerInterface $slugger): \Symfony\Component\HttpFoundation\Response
    {

        $client=new Client();
        $form=$this->createForm(ClientFormulaireType::class,$client);
        $form->handleRequest($request);
        if ($form->isSubmitted() and $form->isValid()){

            $image = $form->get('photo')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($image) {
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
                $client->setImage($newFilename);
            }
            $em=$this->getDoctrine()->getManager();
            $em->persist($client);
            $em->flush();
            return $this->redirectToRoute('clients');
        }
        return $this->render('index.html.twig', [
           "formClient" => $form->createView()
        ]);
    }
    #[Route('/dash/Clients', name: 'Liste_Clients')]
    public function getClients(): Response
    {
        $filtredlist=[];
        $i=0;
        $em=$this->getDoctrine()->getManager();
        $ListeClients=$em->getRepository(User::class)->findAll();
        foreach($ListeClients as $client){
            $roles=$client->getRoles();
            if ($roles[0]!='ROLE_ADMIN'){
                $filtredlist[$i]=$client;
                $i++;
            }
        }
        return $this->render('dashboard/listedesClients.html.twig', [
            "ListeClient" => $filtredlist
        ]);

    }
    /**
     * @Route ("/updateClient/{id}", name="clientUpdate")
     */
    public function  updateClient(Request $request, $id):Response
    {
        $em=$this->getDoctrine()->getManager();
        $client = $em->getRepository("App\Entity\Client")->find($id);
        $editform = $this->createForm(ClientFormulaireType::class, $client);
        $editform->handleRequest($request);
        if($editform->isSubmitted() and $editform->isValid()) {
            $em->persist($client);
            $em->flush();
            return $this->redirectToRoute('client');
        }
        return $this->render('client/updateClient.html.twig',[
            'editFormClient'=>$editform->createView()
        ]);
    }

}
