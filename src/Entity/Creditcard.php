<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CreditcardRepository")
 */
class Creditcard
{
    /**
     * @Groups("creditcard")
     * @Groups("master")
     * @Groups("company")
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Groups("creditcard")
     * @Groups("master")
     * @Groups("company")
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @Groups("creditcard")
     * @ORM\Column(type="string", length=255)
     */
    private $creditCardType;

    /**
     * @Groups("creditcard")
     * @ORM\Column(type="string", length=255)
     */
    private $creditCardNumber;

    /**
     * @Groups("creditcard")
     * @ORM\ManyToOne(targetEntity="App\Entity\Company", inversedBy="creditcards")
     * @ORM\JoinColumn(nullable=false)
     */
    private $company;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getCreditCardType(): ?string
    {
        return $this->creditCardType;
    }

    public function setCreditCardType(string $creditCardType): self
    {
        $this->creditCardType = $creditCardType;

        return $this;
    }

    public function getCreditCardNumber(): ?string
    {
        return $this->creditCardNumber;
    }

    public function setCreditCardNumber(string $creditCardNumber): self
    {
        $this->creditCardNumber = $creditCardNumber;

        return $this;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): self
    {
        $this->company = $company;

        return $this;
    }
}
