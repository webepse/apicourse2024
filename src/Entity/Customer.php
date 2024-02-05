<?php

namespace App\Entity;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use App\Repository\CustomerRepository;
use ApiPlatform\Metadata\GetCollection;
use Doctrine\Common\Collections\Collection;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use Doctrine\Common\Collections\ArrayCollection;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\Link;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CustomerRepository::class)]
#[ApiResource(
    normalizationContext: [
        'groups' => ['customers_read']
    ],
    operations: [
        new Get(),
        new GetCollection(),
        new Post(),
        new Put(),
        new Delete,
        new Patch()
    ]
)]
#[ApiFilter(SearchFilter::class, properties:[
    "firstName" => "partial",
    "lastName",
    "company"
])]
#[ApiFilter(OrderFilter::class)]
class Customer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['customers_read','invoices_read','users_read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['customers_read','invoices_read','users_read'])]
    #[Assert\NotBlank(message: "Le prénom du customer est obligatoire")]
    #[Assert\Length(min: 2, minMessage: "Le prénom doit faire entre 2 et 255 caractères", max: 255, maxMessage: "Le prénom doit faire entre 2 et 255 caractères maximum")]
    private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    #[Groups(['customers_read','invoices_read','users_read'])]
    #[Assert\NotBlank(message: "Le nom du customer est obligatoire")]
    #[Assert\Length(min: 2, minMessage: "Le nom doit faire entre 2 et 255 caractères", max: 255, maxMessage: "Le nom doit faire entre 2 et 255 caractères maximum")]
    private ?string $lastName = null;

    #[ORM\Column(length: 255)]
    #[Groups(['customers_read','invoices_read','users_read'])]
    #[Assert\NotBlank(message:"L'adresse E-mail du customer est obligatoire")]
    #[Assert\Email(message:"Le format de l'adresse E-mail doit être valide")]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['customers_read','invoices_read','users_read'])]
    private ?string $company = null;

    #[ORM\OneToMany(mappedBy: 'customer', targetEntity: Invoice::class)]
    #[Groups(['customers_read', 'users_read'])]
    private Collection $invoices;

    #[ORM\ManyToOne(inversedBy: 'customers')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['customers_read'])]
    private ?User $user = null;

    public function __construct()
    {
        $this->invoices = new ArrayCollection();
    }

    /**
     * Permet de récup le total des invoices
     *
     * @return float
     */
    #[Groups('customers_read')]
    public function getTotalAmount(): float
    {
        return round(array_reduce($this->invoices->toArray(),function($total,$invoice){
            return $total + $invoice->getAmount();
        },0),2);
    }

    /**
     * Permet de récup le montant total non payé des factures
     *
     * @return float
     */
    #[Groups('customers_read')]
    public function getUnpaidAmount(): float
    {
        return round(array_reduce($this->invoices->toArray(),
        function($total,$invoice)
        {
            return $total + ($invoice->getStatus() === "PAID" || $invoice->getStatus() === "CANCELLED" ? 0 : $invoice->getAmount());
        },0),2);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getCompany(): ?string
    {
        return $this->company;
    }

    public function setCompany(?string $company): static
    {
        $this->company = $company;

        return $this;
    }

    /**
     * @return Collection<int, Invoice>
     */
    public function getInvoices(): Collection
    {
        return $this->invoices;
    }

    public function addInvoice(Invoice $invoice): static
    {
        if (!$this->invoices->contains($invoice)) {
            $this->invoices->add($invoice);
            $invoice->setCustomer($this);
        }

        return $this;
    }

    public function removeInvoice(Invoice $invoice): static
    {
        if ($this->invoices->removeElement($invoice)) {
            // set the owning side to null (unless already changed)
            if ($invoice->getCustomer() === $this) {
                $invoice->setCustomer(null);
            }
        }

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }
}
