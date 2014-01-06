<?php

/*
 * This file is part of Phraseanet
 *
 * (c) 2005-2014 Alchemy
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Alchemy\Phrasea\Model\Repositories;

use Doctrine\ORM\EntityRepository;

/**
 * UsrAuthProvider
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class UsrAuthProviderRepository extends EntityRepository
{
    public function findByUser(\User_Adapter $user)
    {
        $dql = 'SELECT u
                FROM Alchemy\Phrasea\Model\Entities\UsrAuthProvider u
                WHERE u.usr_id = :usrId';

        $params = ['usrId' => $user->get_id()];

        $query = $this->_em->createQuery($dql);
        $query->setParameters($params);

        return $query->getResult();
    }

    public function findWithProviderAndId($providerId, $distantId)
    {
        $dql = 'SELECT u
                FROM Alchemy\Phrasea\Model\Entities\UsrAuthProvider u
                WHERE u.provider = :providerId AND u.distant_id = :distantId';

        $params = ['providerId' => $providerId, 'distantId' => $distantId];

        $query = $this->_em->createQuery($dql);
        $query->setParameters($params);

        return $query->getOneOrNullResult();
    }
}
