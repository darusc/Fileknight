<?php

namespace Fileknight\Controller\Traits;

use Fileknight\Entity\User;

trait UserEntityGetterTrait
{
    /**
     * Wrapper method around $security->getUser() that returns
     * it directly cast to a User entity
     */
    public function getUserEntity(): User
    {
        /** @var User $user */
        $user = $this->getUser();

        return $user;
    }
}
