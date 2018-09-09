<?php

namespace App\Controller;

use App\Entity\Company;
use App\Repository\CompanyRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\FOSRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CompanyController extends FOSRestController
{

    private $CompanyRepository;
    private $em;

    public function __construct (CompanyRepository $CompanyRepository, EntityManagerInterface $em)
    {
        $this->CompanyRepository = $CompanyRepository;
        $this->em = $em;
    }

    /**
     * @Rest\Get("companies")
     * @Rest\View(serializerGroups={"company"})
     */
    public function getCompanysAction () {

        //Si ADMIN authentifié, alors on display toutes les compagnies
        //Sinon, on affiche que celle du master authentifié
        //ou bien, on renvoit une erreur
        if ( in_array("ROLE_ADMIN", $this->getUser()->getRoles()) ) {
            $Companys = $this->CompanyRepository->findAll();
            return $this->view($Companys);
        }
        else if ( $this->getUser()->getApiKey() ) {
            $Companys = $this->CompanyRepository->findBy([
                "userId" => $this->getUser()->getId()
            ]);
            return $this->view($Companys);
        }
        else {
            return $this->view('FORBIDDEN');
        }
    }

    /**
     * @Rest\Get("company/{company}")
     * @Rest\View(serializerGroups={"company"})
     */
    public function getCompanyAction(Company $Company) {

        $Company_data = $this->CompanyRepository->find($Company->getId());
        if($Company_data)
            return $this->view($Company_data);
    }

    /**
     * @Rest\Post("company/{company}")
     * @Rest\View(serializerGroups={"company"})
     * @Rest\Post("/company")
     * @ParamConverter("company", converter="fos_rest.request_body")
     */
    public function postCompanyAction(Company $company, ValidatorInterface $validator) {

        if ( $this->getUser()->getApiKey() )
        {
            $company->setMaster($this->getUser());
        }

        $errors = $validator->validate($company);

        if (count($errors) > 0)
        {
            return $this->view("Error: Invalid JSON data.");
        }
        else {
            $this->em->persist($company);
            $this->em->flush();
            return $this->view($company);
        }
    }

    /**
     * @Rest\Put("company/{company}")
     * @Rest\View(serializerGroups={"company"})
     */
    public function putCompanyAction (Request $request) {

        if ( $this->getUser()->getApiKey() ) {

            $Company_data = $this->CompanyRepository->find($this->getUser()->getCompany->getApiKey());

            if ($name = $request->get('name')) {
                $Company_data->setName($name);
            }
            if ($slogan = $request->get('slogan')) {
                $Company_data->setSlogan($slogan);
            }
            if ($phoneNumber = $request->get('phoneNumber'))
            {
                $Company_data->setPhoneNumber($phoneNumber);
            }
            if ($address = $request->get('address'))
            {
                $Company_data->setAddress($address);
            }

            $this->em->persist($Company_data);
            $this->em->flush();
            return $this->view($Company_data);
        }
        else {
            return $this->view('FORBIDDEN');
        }
    }

    /**
     * @Rest\Put("company/{id}")
     * @Rest\View(serializerGroups={"company"})
     */
    public function putCompaniesAction (Request $request, int $id) {

        $Company_data = $this->CompanyRepository->find($id);

        if ( $this->getUser()->getApiKey() === $Company_data->getMaster()->getApiKey() || in_array("ROLE_ADMIN", $this->getUser()->getRoles()) ) {

            if ($name = $request->get('name')) {
                $Company_data->setName($name);
            }
            if ($slogan = $request->get('slogan')) {
                $Company_data->setSlogan($slogan);
            }
            if ($phoneNumber = $request->get('phoneNumber'))
            {
                $Company_data->setPhoneNumber($phoneNumber);
            }
            if ($address = $request->get('address'))
            {
                $Company_data->setAddress($address);
            }

            $this->em->persist($Company_data);
            $this->em->flush();
            return $this->view($Company_data);
        }
        else {
            return $this->view('FORBIDDEN');
        }
    }

    /**
     * @Rest\Delete("company")
     * @Rest\View(serializerGroups={"company"})
     */
    public function deleteCompanyAction () {

        if ( $this->getUser()->getApiKey() ) {

            $company_data = $this->CompanyRepository->find($this->getUser());

            $this->em->remove($company_data);
            $this->em->flush();
            return $this->view($company_data);
        }
        else {
            return $this->view( "FORBIDDEN!" );
        }
    }

    /**
     * @Rest\Delete("company/{company}")
     * @Rest\View(serializerGroups={"company"})
     */
    public function deleteCompanysAction (Company $company) {

        if ( in_array("ROLE_ADMIN", $this->getUser()->getRoles()) ) {
            $this->em->remove($company);
            $this->em->flush();
            return $this->view($company);
        }
        else {
            return $this->view( "FORBIDDEN!" );
        }
    }

}