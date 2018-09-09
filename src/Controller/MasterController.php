<?php

namespace App\Controller;

use App\Entity\Master;
use App\Repository\MasterRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Swagger\Annotations as SWG;

class MasterController extends FOSRestController
{
    private $masterRepository;
    private $em;

    public function __construct (MasterRepository $masterRepository, EntityManagerInterface $em)
    {
        $this->masterRepository = $masterRepository;
        $this->em = $em;
    }

    /**
     * @Rest\View(serializerGroups={"master"})
     */
    public function getMastersAction () {

        if ( in_array("ROLE_ADMIN", $this->getUser()->getRoles()) ) {
            $masters = $this->masterRepository->findAll();
            return $this->view($masters);
        }
        else {
            $data = array(
                'message' => ["Access forbidden!"]
            );
            return new JsonResponse( $data, Response:: HTTP_FORBIDDEN);
        }

    }

    /**
     * @Rest\Get("master/{master}")
     * @Rest\View(serializerGroups={"master"})
     */
    public function getMasterAction(Master $master) {

            $master_data = $this->masterRepository->find($master->getId());
            if ($master_data)
                return $this->view($master_data);
    }

    /**
     * @Rest\View(serializerGroups={"master"})
     * @Rest\Post("/master")
     * @ParamConverter("master", converter="fos_rest.request_body")
     */
    public function postMasterAction(Master $master, ValidatorInterface $validator) {

            $errors = $validator->validate($master);

            if (count($errors) > 0)
            {
                return $this->view("Error: Invalid or missing JSON data");
            }
            else {
                $this->em->persist($master);
                $this->em->flush();
                return $this->view($master);
            }
    }

    /**
     * @Rest\Put("master/{id}")
     * @Rest\View(serializerGroups={"master"})
     */
    public function putMasterAction (Request $request, int $id) {

        //Modification du compte Master, soit par ADMIN soit par le master en question
        $master_data = $this->masterRepository->find($id);

        if ( $this->getUser()->getApiKey() === $master_data->getApiKey() || in_array("ROLE_ADMIN", $this->getUser()->getRoles()) ) {

            //Edition des informations
            if ($firstname = $request->get('firstname')) {
                $master_data->setFirstname($firstname);
            }
            if ($lastname = $request->get('lastname')) {
                $master_data->setFirstname($lastname);
            }
            if ($email = $request->get('email')) {
                $master_data->setEmail($email);
            }

            //On persiste dans la DB
            $this->em->persist($master_data);
            $this->em->flush();
            return $this->view($master_data);
        }
        else {
            $data = array(
                'message' => ["Access forbidden!"]
            );
            return new JsonResponse( $data, Response:: HTTP_FORBIDDEN);
        }

    }

    /**
     * @Rest\Delete("master")
     * @Rest\View(serializerGroups={"master"})
     */
    public function deleteMasterAction () {

        if ( $this->getUser()->getApiKey() ) {

            $master_data = $this->masterRepository->find($this->getUser());

            $this->em->remove($master_data);
            $this->em->flush();
            return $this->view($master_data);
        }
        else {
            $data = array(
                'message' => ["Access forbidden!"]
            );
            return new JsonResponse( $data, Response:: HTTP_FORBIDDEN);
        }

    }

    /**
     * @Rest\Delete("master/{master}")
     * @Rest\View(serializerGroups={"master"})
     */
    public function deleteMastersAction (Master $master) {

        //Method de suppression d'un master, seulement par un ADMIN

        if ( in_array("ROLE_ADMIN", $this->getUser()->getRoles()) ) {
            $this->em->remove($master);
            $this->em->flush();
            return $this->view($master);
        }
        else {
            $data = array(
                'message' => ["Access forbidden!"]
            );
            return new JsonResponse( $data, Response:: HTTP_FORBIDDEN);
        }

    }
}