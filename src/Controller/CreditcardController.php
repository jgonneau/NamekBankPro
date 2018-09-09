<?php

namespace App\Controller;

use App\Entity\Creditcard;
use App\Repository\CreditcardRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\FOSRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CreditcardController extends FOSRestController
{

    private $creditcardRepository;
    private $em;

    public function __construct (CreditcardRepository $creditcardRepository, EntityManagerInterface $em)
    {
        $this->creditcardRepository = $creditcardRepository;
        $this->em = $em;
    }

    /**
     * @Rest\Get("creditcards")
     * @Rest\View(serializerGroups={"creditcard"})
     */
    public function getCreditcardsAction () {

        if ( in_array("ROLE_ADMIN", $this->getUser()->getRoles()) ) {

            //Si ADMIN authentifié, alors il peut recevoir la liste de toutes les creditcards
            $creditcards = $this->creditcardRepository->findAll();
            return $this->view($creditcards);
        }
        else if ( $this->getUser()->getApiKey() ) {

            //Si Master Authentifié, alors le Master peut recevoir la liste des creditcards de sa company
            $creditcards = $this->creditcardRepository->findBy([
                "company" => $this->getUser()->getCompany()
            ]);
            return $this->view($creditcards);
        }
        else {
            //Sinon, renvoie une erreur.
            return $this->view('FORBIDDEN');
        }
    }

    /**
     * @Rest\Get("creditcard/{creditcard}")
     * @Rest\View(serializerGroups={"creditcard"})
     */
    public function getCreditcardAction(Creditcard $creditcard) {


        if ( in_array("ROLE_ADMIN", $this->getUser()->getRoles()) ) {

            //Seulement si ADMIN authentifié, alors il peut recuperer les infos d'une creditcard
            $creditcard_data = $this->creditcardRepository->find($creditcard->getId());
            if ($creditcard_data)
                return $this->view($creditcard_data);
        }
        else {
            $data = array(
                'message' => ["Access forbidden!"]
            );
            return new JsonResponse( $data, Response:: HTTP_FORBIDDEN);
        }

    }

    /**
     * @Rest\View(serializerGroups={"creditcard"})
     * @Rest\Post("/creditcard")
     * @ParamConverter("creditcard", converter="fos_rest.request_body")
     */
    public function postCreditcardAction(Creditcard $creditcard, ValidatorInterface $validator) {

        //Si le Master est authentifié, il peut créer une credit card pour sa company
        if ( $this->getUser()->getApiKey() ) {

            $errors = $validator->validate($creditcard);

            if (count($errors) > 0)
            {
                return $this->view("Error: Invalid or missing JSON data");
            }
            else {

                //L'on affecte la credit card à la Company du Master
                $creditcard->setCompany($this->getUser()->getCompany());

                //L'on persist dans la BDD
                $this->em->persist($creditcard);
                $this->em->flush();
                return $this->view($creditcard);
            }
        }
        else {
            return $this->view('FORBIDDEN');
        }
    }

    /**
     * @Rest\Put("creditcard")
     * @Rest\View(serializerGroups={"creditcard"})
     */
    public function putCreditcardAction (Request $request) {

        // Si Master authentifié, il peut modifier une de ses creditcards
        // Par contre ici, il faudra obligatoirement l'id

        if ( $this->getUser()->getApiKey() ) {

            //L'on recupere la creditcard qui est appartenant à la company du master

            $creditcard_data = $this->creditcardRepository->findBy([
                "company" => $this->getUser()->getCompany(),
                "id" => $request->get('id'),
            ]);

            //Si la creditcard existe, l'on peut la modifier
            if ($creditcard_data) {

                if ($name = $request->get('name')) {
                    $creditcard_data->setName($name);
                }

                if ($creditcardType = $request->get('creditCardType')) {
                    $creditcard_data->setCreditCardType($creditcardType);
                }

                if ($creditcardNumber = $request->get('creditCardNumber')) {
                    $creditcard_data->setCreditCardNumber($creditcardNumber);
                }

                $this->em->persist($creditcard_data);
                $this->em->flush();
                return $this->view($creditcard_data);
            }
            else {
                $data = array(
                    'message' => ["No credit card found!"]
                );
                return new JsonResponse( $data, Response::HTTP_NOT_FOUND);
            }
        }
        else {
            return $this->view('FORBIDDEN');
        }
    }

    /**
     * @Rest\Put("creditcard/{id}")
     * @Rest\View(serializerGroups={"creditcard"})
     */
    public function putCreditcardsAction (Request $request, int $id) {


        if ( in_array("ROLE_ADMIN", $this->getUser()->getRoles()) ) {

            $creditcard_data = $this->creditcardRepository->find($id);

            if ($name = $request->get('name')) {
                $creditcard_data->setName($name);
            }

            if ($creditcardType = $request->get('creditCardType')) {
                $creditcard_data->setCreditCardType($creditcardType);
            }

            if ($creditcardNumber = $request->get('creditCardNumber')) {
                $creditcard_data->setCreditCardNumber($creditcardNumber);
            }

            $this->em->persist($creditcard_data);
            $this->em->flush();
            return $this->view($creditcard_data);
        }
        else {
            return $this->view('FORBIDDEN');
        }
    }

    /**
     * @Rest\Delete("creditcard/{creditcard}")
     * @Rest\View(serializerGroups={"creditcard"})
     */
    public function deleteCreditcardAction (Creditcard $creditcard) {

        //Si Master authentifié et que la creditcard est relié à sa company, alors il peut la supprimer
        //Aussi, si ADMIN authentifié, il peut supprimer la creditcard

        if ( $this->getUser()->getApiKey() === $creditcard->getCompany()->getMaster()->getApiKey() || in_array("ROLE_ADMIN", $this->getUser()->getRoles()) ) {
            $this->em->remove($creditcard);
            $this->em->flush();
            return $this->view($creditcard);
        }
        else {
            return $this->view( "FORBIDDEN!" );
        }
    }

}