<?php

namespace App\Doctrine;

use App\Entity\User;
use App\Entity\Invoice;
use App\Entity\Customer;

use Doctrine\ORM\QueryBuilder;
use ApiPlatform\Metadata\Operation;
use Symfony\Bundle\SecurityBundle\Security;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;



class CurrentUserExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface{
    private $security;
    private $auth;

    public function __construct(Security $security, AuthorizationCheckerInterface $checker)
    {
        $this->security = $security;
        $this->auth = $checker;
    }

    private function addWhere(QueryBuilder $queryBuilder, string $resourceClass){
        // obtenir l'utilisateur connecté
        $user = $this->security->getUser();
        // si on demande des invoices ou des customers, alors agir sur la requête pour qu'elle tienne compte de l'utilisateur connecté 
        if(($resourceClass === Customer::class || $resourceClass === Invoice::class) && !$this->auth->isGranted('ROLE_ADMIN') && $user instanceof User){
            $rootAlias = $queryBuilder->getRootAliases()[0]; // permet de récupèrer l'alias de la queryBuilder. Attention, ici on récupère un tableau et on veut le 1er

            if($resourceClass === Customer::class){
                $queryBuilder->andWhere("$rootAlias.user = :user"); // on veut que ça soit relié à notre utilisateur connecté
            } elseif($resourceClass === Invoice::class){
                $queryBuilder->join("$rootAlias.customer", "c")
                            ->andWhere("c.user = :user"); // on veut voir les factures de notre utilisateur connecté
            }

            $queryBuilder->setParameter("user", $user);

        }
    }

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, Operation $operation = null, array $context = []): void
    {
        $this->addWhere($queryBuilder, $resourceClass);
    }

    public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, Operation $operation = null, array $context = []): void
    {
        $this->addWhere($queryBuilder, $resourceClass);
    }


}