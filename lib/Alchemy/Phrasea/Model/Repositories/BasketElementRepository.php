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

use Alchemy\Phrasea\Model\Entities\BasketElement;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * BasketElementRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class BasketElementRepository extends EntityRepository
{

    public function findUserElement($element_id, \User_Adapter $user)
    {
        $dql = 'SELECT e
            FROM Alchemy\Phrasea\Model\Entities\BasketElement e
            JOIN e.basket b
            LEFT JOIN e.validation_datas vd
            LEFT JOIN b.validation s
            LEFT JOIN s.participants p
            WHERE (b.usr_id = :usr_id OR p.usr_id = :same_usr_id)
              AND e.id = :element_id';

        $params = [
            'usr_id'      => $user->get_id(),
            'same_usr_id' => $user->get_id(),
            'element_id'  => $element_id
        ];

        $query = $this->_em->createQuery($dql);
        $query->setParameters($params);

        $element = $query->getOneOrNullResult();

        /* @var $element BasketElement */
        if (null === $element) {
            throw new NotFoundHttpException('Element is not found');
        }

        return $element;
    }

    public function findElementsByRecord(\record_adapter $record)
    {
        $dql = 'SELECT e
            FROM Alchemy\Phrasea\Model\Entities\BasketElement e
            JOIN e.basket b
            LEFT JOIN b.validation s
            LEFT JOIN s.participants p
            WHERE e.record_id = :record_id
            AND e.sbas_id = :sbas_id';

        $params = [
            'sbas_id'   => $record->get_sbas_id(),
            'record_id' => $record->get_record_id()
        ];

        $query = $this->_em->createQuery($dql);
        $query->setParameters($params);

        return $query->getResult();
    }

    public function findElementsByDatabox(\databox $databox)
    {
        $dql = 'SELECT e
            FROM Alchemy\Phrasea\Model\Entities\BasketElement e
            JOIN e.basket b
            LEFT JOIN b.validation s
            LEFT JOIN s.participants p
            WHERE e.sbas_id = :sbas_id';

        $params = [
            'sbas_id'   => $databox->get_sbas_id(),
        ];

        $query = $this->_em->createQuery($dql);
        $query->setParameters($params);

        return $query->getResult();
    }

    /**
     *
     * @param  \record_adapter                              $record
     * @param  \User_Adapter                                $user
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function findReceivedElementsByRecord(\record_adapter $record, \User_Adapter $user)
    {
        $dql = 'SELECT e
            FROM Alchemy\Phrasea\Model\Entities\BasketElement e
            JOIN e.basket b
            LEFT JOIN b.validation s
            LEFT JOIN s.participants p
            WHERE b.usr_id = :usr_id
            AND b.pusher_id IS NOT NULL
            AND e.record_id = :record_id
            AND e.sbas_id = :sbas_id';

        $params = [
            'sbas_id'   => $record->get_sbas_id(),
            'record_id' => $record->get_record_id(),
            'usr_id'    => $user->get_id()
        ];

        $query = $this->_em->createQuery($dql);
        $query->setParameters($params);

        return $query->getResult();
    }

    public function findReceivedValidationElementsByRecord(\record_adapter $record, \User_Adapter $user)
    {
        $dql = 'SELECT e
            FROM Alchemy\Phrasea\Model\Entities\BasketElement e
            JOIN e.basket b
            JOIN b.validation v
            JOIN v.participants p
            WHERE p.usr_id = :usr_id
            AND e.record_id = :record_id
            AND e.sbas_id = :sbas_id';

        $params = [
            'sbas_id'   => $record->get_sbas_id(),
            'record_id' => $record->get_record_id(),
            'usr_id'    => $user->get_id()
        ];

        $query = $this->_em->createQuery($dql);
        $query->setParameters($params);

        return $query->getResult();
    }
}
