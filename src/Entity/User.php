<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;


/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\Table(name="`user`")
 */
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $prenom;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $telephone;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $promotion;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $adresse;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $roles;

    /**
     * @ORM\OneToMany(targetEntity=Cotisation::class, mappedBy="utilisateur")
     */
    private $cotisations;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $photo;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $profession;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $anneeDebut;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $anneeFin;

    public function __construct()
    {
        $this->cotisations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): self
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getPromotion(): ?string
    {
        return $this->promotion;
    }

    public function setPromotion(string $promotion): self
    {
        $this->promotion = $promotion;

        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(?string $adresse): self
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getRoles(): array
    {
        // Si le champ en base est juste "ROLE_ADMIN" ou "ROLE_USER"
        // on le transforme en tableau
        if (is_string($this->roles)) {
            $roles = [$this->roles];
        } else {
            $roles = $this->roles ?? [];
        }

        // Toujours inclure ROLE_USER au minimum
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }



    public function setRoles(array $roles): self
    {
        $this->roles = json_encode($roles);
        return $this;
    }


    /**
     * @return Collection<int, Cotisation>
     */
    public function getCotisations(): Collection
    {
        return $this->cotisations;
    }

    public function addCotisation(Cotisation $cotisation): self
    {
        if (!$this->cotisations->contains($cotisation)) {
            $this->cotisations[] = $cotisation;
            $cotisation->setUtilisateur($this);
        }

        return $this;
    }

    public function removeCotisation(Cotisation $cotisation): self
    {
        if ($this->cotisations->removeElement($cotisation)) {
            // set the owning side to null (unless already changed)
            if ($cotisation->getUtilisateur() === $this) {
                $cotisation->setUtilisateur(null);
            }
        }

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

   
    public function getUsername(): string
    {
        return (string) $this->email;  // Utilisez l'email comme nom d'utilisateur
    }

    public function getSalt(): ?string
    {
        // Aucune implémentation nécessaire si vous utilisez bcrypt ou argon2 pour le hachage
        return null;
    }

    public function eraseCredentials(): void
    {
        // Si vous stockez des données sensibles temporairement, nettoyez-les ici
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): self
    {
        $this->photo = $photo;

        return $this;
    }

    public function getProfession(): ?string
    {
        return $this->profession;
    }

    public function setProfession(?string $profession): self
    {
        $this->profession = $profession;

        return $this;
    }

    public function getAnneeDebut(): ?string
    {
        return $this->anneeDebut;
    }

    public function setAnneeDebut(?string $anneeDebut): self
    {
        $this->anneeDebut = $anneeDebut;

        return $this;
    }

    public function getAnneeFin(): ?string
    {
        return $this->anneeFin;
    }

    public function setAnneeFin(?string $anneeFin): self
    {
        $this->anneeFin = $anneeFin;

        return $this;
    }
}
