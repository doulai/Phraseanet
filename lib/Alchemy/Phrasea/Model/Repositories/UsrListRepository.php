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

use Alchemy\Phrasea\Application;
use Alchemy\Phrasea\Model\Entities\UsrList;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * UsrListRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class UsrListRepository extends EntityRepository
{

    /**
     * Get all lists readable for a given User
     *
     * @param  \User_Adapter                                $user
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function findUserLists(\User_Adapter $user)
    {
        $dql = 'SELECT l FROM Alchemy\Phrasea\Model\Entities\UsrList l
              JOIN l.owners o
            WHERE o.usr_id = :usr_id';

        $params = [
            'usr_id' => $user->get_id(),
        ];

        $query = $this->_em->createQuery($dql);
        $query->setParameters($params);

        return $query->getResult();
    }

    /**
     *
     * @param  \User_Adapter $user
     * @param  type          $list_id
     * @return UsrList
     */
    public function findUserListByUserAndId(Application $app, \User_Adapter $user, $list_id)
    {
        $list = $this->find($list_id);

        /* @var $list UsrList */
        if (null === $list) {
            throw new NotFoundHttpException('List is not found.');
        }

        if ( ! $list->hasAccess($user, $app)) {
            throw new AccessDeniedHttpException('You have not access to this list.');
        }

        return $list;
    }

    /**
     * Search for a UsrList like '' with a given value, for a user
     *
     * @param  \User_Adapter                                $user
     * @param  type                                         $name
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function findUserListLike(\User_Adapter $user, $name)
    {
        $dql = 'SELECT l FROM Alchemy\Phrasea\Model\Entities\UsrList l
              JOIN l.owners o
            WHERE o.usr_id = :usr_id AND l.name LIKE :name';

        $params = [
            'usr_id' => $user->get_id(),
            'name'   => $name . '%'
        ];

        $query = $this->_em->createQuery($dql);
        $query->setParameters($params);

        return $query->getResult();
    }
}
