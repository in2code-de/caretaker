<?php

namespace Caretaker\Caretaker\Repository;

use Caretaker\Caretaker\Entity\Node\AbstractNode;
use Caretaker\Caretaker\Entity\Node\AggregatorNode;
use Caretaker\Caretaker\Entity\Result\AggregatorResult;
use Caretaker\Caretaker\Entity\Result\AggregatorResultRange;
use Caretaker\Caretaker\Entity\Result\ResultMessage;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/***************************************************************
 * Copyright notice
 *
 * (c) 2009-2011 by n@work GmbH and networkteam GmbH
 *
 * All rights reserved
 *
 * This script is part of the Caretaker project. The Caretaker project
 * is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * This is a file of the caretaker project.
 * http://forge.typo3.org/projects/show/extension-caretaker
 *
 * Project sponsored by:
 * n@work GmbH - http://www.work.de
 * networkteam GmbH - http://www.networkteam.com/
 *
 * $Id$
 */

/**
 * Repository to handle the storing and reconstruction of all
 * aggregatorResults. The whole object <-> database
 * communication happens here.
 *
 * @author Martin Ficzel <martin@work.de>
 * @author Thomas Hempel <thomas@work.de>
 * @author Christopher Hlubek <hlubek@networkteam.com>
 * @author Tobias Liebig <liebig@networkteam.com>
 *
 */
class AggregatorResultRepository
{
    /**
     * Reference to the current Instance
     *
     * @var $instance TestResultRepository
     */
    private static $instance = null;

    /**
     * Private constructor use getInstance instead
     */
    private function __construct()
    {
    }

    /**
     * Get the Singleton Object
     *
     * @return AggregatorResultRepository
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Get the latest Testresults for the given Node Object
     *
     * @param AbstractNode $node
     * @return AggregatorResult
     */
    public function getLatestByNode($node)
    {
        $instance = $node->getInstance();
        if ($instance) {
            $instanceUid = $instance->getUid();
        } else {
            $instanceUid = 0;
        }

        $nodeType = $node->getType();
        $nodeUid = $node->getUid();

        $queryBuilder = $this->createBaseQueryBuilder($nodeUid, $nodeType, $instanceUid);
        $queryResult = $queryBuilder
            ->orderBy('tstamp', 'DESC')
            ->setMaxResults(1)
            ->executeQuery();

        while ($row = $queryResult->fetchAssociative()) {
            $result = $this->dbrow2instance($row);
            return $result;
        }

        return new AggregatorResult();
    }

    /**
     * Get the ResultRange for the given Aggregator and the timerange
     *
     * @param AbstractNode $node
     * @param int $start_timestamp
     * @param int $stop_timestamp
     * @return AggregatorResultRange
     */
    public function getRangeByNode($node, $start_timestamp, $stop_timestamp)
    {
        $result_range = new AggregatorResultRange($start_timestamp, $stop_timestamp);

        $instance = $node->getInstance();
        if ($instance) {
            $instanceUid = $instance->getUid();
        } else {
            $instanceUid = 0;
        }

        $nodeType = $node->getType();
        $nodeUid = $node->getUid();

        $queryBuilder = $this->createBaseQueryBuilder($nodeUid, $nodeType, $instanceUid);
        $queryBuilder
            ->andWhere(
                $queryBuilder->expr()->gte('tstamp', $queryBuilder->createNamedParameter($start_timestamp, \PDO::PARAM_INT)),
                $queryBuilder->expr()->lte('tstamp', $queryBuilder->createNamedParameter($stop_timestamp, \PDO::PARAM_INT))
            )
            ->orderBy('tstamp', 'ASC');

        $resultRows = $queryBuilder->executeQuery();

        while ($row = $resultRows->fetchAssociative()) {
            $result = $this->dbrow2instance($row);
            $result_range->addResult($result);
        }

        // add first value if needed
        $first = $result_range->getFirst();
        if (!$first || ($first && $first->getTimestamp() > $start_timestamp)) {
            $queryBuilder = $this->createBaseQueryBuilder($nodeUid, $nodeType, $instanceUid);
            $queryBuilder
                ->andWhere(
                    $queryBuilder->expr()->lt('tstamp', $queryBuilder->createNamedParameter($start_timestamp, \PDO::PARAM_INT))
                )
                ->orderBy('tstamp', 'DESC')
                ->setMaxResults(1);

            $resultRows = $queryBuilder->executeQuery();

            while ($row = $resultRows->fetchAssociative()) {
                $row['tstamp'] = $start_timestamp;
                $result = $this->dbrow2instance($row);
                $result_range->addResult($result);
            }
        }

        // add last value if needed
        /** @var AggregatorResult $last */
        $last = $result_range->getLast();
        if ($last && $last->getTimestamp() < $stop_timestamp) {
            $real_last = new AggregatorResult($stop_timestamp, $last->getState(), $last->getNumUNDEFINED(), $last->getNumOK(), $last->getNumWARNING(), $last->getNumERROR(), $last->getMessage()->getText());
            $result_range->addResult($real_last);
        }

        return $result_range;
    }

    /**
     *
     * @param AggregatorNode $node
     * @return int
     */
    public function getResultNumberByNode($node)
    {
        $instance = $node->getInstance();
        if ($instance) {
            $instanceUid = $instance->getUid();
        } else {
            $instanceUid = 0;
        }

        $nodeType = $node->getType();
        $nodeUid = $node->getUid();

        $queryBuilder = $this->createBaseQueryBuilder($nodeUid, $nodeType, $instanceUid);
        $queryBuilder
            ->select('COUNT(*) AS number')
            ->setMaxResults(1);

        $row = $queryBuilder->executeQuery()->fetchAssociative();

        if ($row) {
            return (int) $row['number'];
        }
        return 0;
    }

    /**
     * @param AbstractNode $node
     * @param int $offset
     * @param int $limit
     * @return AggregatorResultRange
     */
    public function getResultRangeByNodeAndOffset($node, $offset = 0, $limit = 10)
    {
        $result_range = new AggregatorResultRange(null, null);

        $instance = $node->getInstance();
        if ($instance) {
            $instanceUid = $instance->getUid();
        } else {
            $instanceUid = 0;
        }

        $nodeType = $node->getType();
        $nodeUid = $node->getUid();

        $queryBuilder = $this->createBaseQueryBuilder($nodeUid, $nodeType, $instanceUid);
        $queryBuilder
            ->orderBy('tstamp', 'DESC')
            ->setFirstResult((int)$offset)
            ->setMaxResults((int)$limit);

        $resultRows = $queryBuilder->executeQuery();
        while ($row = $resultRows->fetchAssociative()) {
            $result = $this->dbrow2instance($row);
            $result_range->addResult($result);
        }

        return $result_range;
    }

    /**
     * Save Aggregator Result to the DB
     *
     * @param AggregatorNode $node
     * @param AggregatorResult $aggregator_result
     * @return int UID of the new DB result Record
     */
    public function addNodeResult(AggregatorNode $node, AggregatorResult $aggregator_result)
    {
        //add an undefined row to the testresult column
        $instance = $node->getInstance();
        if ($instance) {
            $instanceUid = $instance->getUid();
        } else {
            $instanceUid = 0;
        }

        $values = array(
            'aggregator_uid' => $node->getUid(),
            'aggregator_type' => $node->getType(),
            'instance_uid' => $instanceUid,

            'result_status' => $aggregator_result->getState(),
            'tstamp' => $aggregator_result->getTimestamp(),
            'result_num_undefined' => $aggregator_result->getNumUNDEFINED(),
            'result_num_ok' => $aggregator_result->getNumOK(),
            'result_num_warnig' => $aggregator_result->getNumWARNING(),
            'result_num_error' => $aggregator_result->getNumERROR(),
            'result_msg' => $aggregator_result->getMessage()->getText(),
            'result_values' => serialize($aggregator_result->getMessage()->getValues()),
            'result_submessages' => serialize($aggregator_result->getSubMessages()),
        );
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tx_caretaker_aggregatorresult');
        $connection->insert('tx_caretaker_aggregatorresult', $values);

        return $connection->lastInsertId();
    }

    /**
     * Convert DB-Row to Aggregator Node Result
     *
     * @param array $row DB Row
     * @return AggregatorResult
     */
    private function dbrow2instance($row)
    {
        $message = new ResultMessage($row['result_msg'], unserialize($row['result_values']));
        $submessages = ($row['result_submessages']) ? unserialize($row['result_submessages']) : array();
        $instance = new AggregatorResult(
            $row['tstamp'],
            $row['result_status'],
            $row['result_num_undefined'],
            $row['result_num_ok'],
            $row['result_num_warning'],
            $row['result_num_error'],
            $message,
            $submessages
        );

        return $instance;
    }

    private function createBaseQueryBuilder($nodeUid, $nodeType, $instanceUid) {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tx_caretaker_aggregatorresult');

        $queryBuilder
            ->select('*')
            ->from('tx_caretaker_aggregatorresult')
            ->where(
                $queryBuilder->expr()->eq('aggregator_uid', $queryBuilder->createNamedParameter($nodeUid, \PDO::PARAM_INT)),
                $queryBuilder->expr()->eq('aggregator_type', $queryBuilder->createNamedParameter($nodeType)),
                $queryBuilder->expr()->eq('instance_uid', $queryBuilder->createNamedParameter($instanceUid, \PDO::PARAM_INT))
            );

        return $queryBuilder;
    }
}
