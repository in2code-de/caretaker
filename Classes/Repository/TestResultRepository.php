<?php

namespace Caretaker\Caretaker\Repository;

/***************************************************************
 * Copyright notice
 *
 * (c) 2009-2011 by n@work GmbH and networkteam GmbH
 * (c) 2023 by in2code GmbH
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
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Database\ConnectionPool;
use Caretaker\Caretaker\Entity\Result\ResultMessage;
use Caretaker\Caretaker\Entity\Result\TestResult;
use Caretaker\Caretaker\Entity\Node\AbstractNode;
use Caretaker\Caretaker\Entity\Node\TestNode;
use Caretaker\Caretaker\Entity\Result\TestResultRange;

/**
 * Repository to handle the storing and reconstruction of all
 * testResults. The whole object <-> database
 * communication happens here.
 *
 * @author Martin Ficzel <martin@work.de>
 * @author Thomas Hempel <thomas@work.de>
 * @author Christopher Hlubek <hlubek@networkteam.com>
 * @author Tobias Liebig <liebig@networkteam.com>
 *
 */
class TestResultRepository
{
    /**
     * Reference to the current Instance
     *
     * @var $instance TestResultRepository
     */
    private static $instance = null;

    /**
     * The time in seconds to search for the last node result
     *
     * @var int
     */
    private $lastTestResultScanRange = 0;

    /**
     * Private constructor use getInstance instead
     */
    private function __construct(private readonly ConnectionPool $connectionPool)
    {
        $confArray = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('caretaker');
        $this->lastTestResultScanRange = (int)$confArray['lastTestResultScanRange'];
    }

    /**
     * Get the Singleton Object
     *
     * @return TestResultRepository
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Get the latest Testresult for the given Instance and Test
     *
     * @param TestNode $testNode
     * @return TestResult
     */
    public function getLatestByNode(TestNode $testNode)
    {
        $testUID = $testNode->getUid();
        $instanceUID = $testNode->getInstance()->getUid();
        $row = $this->connectionPool
            ->getConnectionForTable('tx_caretaker_lasttestresult')
            ->select(
                '*',
                'tx_caretaker_lasttestresult',
                [
                    'test_uid' => $testUID,
                    'instance_uid'=> $instanceUID
                ])
            ->fetchAssociative();

        if ($row) {
            $result = $this->dbrow2instance($row);

            return $result;
        }
        return new TestResult();
    }

    /**
     * Get the latest Testresult for the given Instance and Test
     *
     * @param AbstractNode $testNode
     * @param $currentResult
     * @return TestResult
     */
    public function getPreviousDifferingResult($testNode, $currentResult)
    {
        $row = null;
        if ($testNode instanceof TestNode) {
            $testUID = $testNode->getUid();
            $instanceUID = $testNode->getInstance()->getUid();

            $row = $this->connectionPool
                ->getConnectionForTable('tx_caretaker_testresult')
                ->select(
                    '*',
                    'tx_caretaker_testresult',
                    [
                        'test_uid' => $testUID,
                        'instance_uid'=> $instanceUID,

                    ])
                ->fetchAssociative();

            $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
                '*',
                'tx_caretaker_testresult',
                'test_uid = ' . $testUID .
                ' AND instance_uid = ' . $instanceUID .
                ' AND result_status <> ' . $currentResult->getState() .
                ' AND tstamp < ' . $currentResult->getTimestamp(),
                'tstamp DESC, uid DESC',
                '',
                '1'
            );
            $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
        }

        if ($row) {
            $result = $this->dbrow2instance($row);

            return $result;
        }
        return new TestResult();
    }

    /**
     * Return the Number of available TestResults
     *
     * @param  TestNode $testNode
     * @return int
     */
    public function getResultNumberByNode(TestNode $testNode)
    {
        $testUID = $testNode->getUid();
        $instanceUID = $testNode->getInstance()->getUid();

        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('COUNT(*) AS number', 'tx_caretaker_testresult', 'test_uid=' . $testUID . ' AND instance_uid=' . $instanceUID, '', '', '1');
        $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);

        if ($row) {
            return (int)$row['number'];
        }
        return 0;
    }

    /**
     * Get a List of Testresults defined by Offset and Limit
     *
     * @param TestNode $testNode
     * @param int $offset
     * @param int $limit
     * @return TestResultRange
     */
    public function getResultRangeByNodeAndOffset(TestNode $testNode, $offset = 0, $limit = 10)
    {
        $testUID = $testNode->getUid();
        $instanceUID = $testNode->getInstance()->getUid();

        $result_range = new TestResultRange(null, null);
        $base_condition = 'test_uid=' . $testUID . ' AND instance_uid=' . $instanceUID . ' ';

        $GLOBALS['TYPO3_DB']->store_lastBuiltQuery = true;
        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_caretaker_testresult', $base_condition, '', 'tstamp DESC', (int)$offset . ',' . (int)$limit);

        while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
            $result = $this->dbrow2instance($row);
            $result_range->addResult($result);
        }

        return $result_range;
    }

    /**
     * Get the ResultRange for the given Instance Test and the timerange
     *
     * @param TestNode $testNode
     * @param int $start_timestamp
     * @param int $stop_timestamp
     * @param bool $graph By default the result range is created for the graph, so the last result is added again at the end
     * @return TestResultRange
     */
    public function getRangeByNode(TestNode $testNode, $start_timestamp, $stop_timestamp, $graph = true)
    {
        $testUID = $testNode->getUid();
        $instanceUID = $testNode->getInstance()->getUid();

        $result_range = new TestResultRange($start_timestamp, $stop_timestamp);
        $base_condition = 'test_uid=' . $testUID . ' AND instance_uid=' . $instanceUID . ' ';

        $GLOBALS['TYPO3_DB']->store_lastBuiltQuery = true;
        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_caretaker_testresult', $base_condition . 'AND tstamp >=' . $start_timestamp . ' AND tstamp <=' . $stop_timestamp, '', 'tstamp ASC');

        while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
            $result = $this->dbrow2instance($row);
            $result_range->addResult($result);
        }

        // add first value if needed
        $first = $result_range->getFirst();
        if (!$first || ($first && $first->getTimestamp() > $start_timestamp)) {
            $GLOBALS['TYPO3_DB']->store_lastBuiltQuery = true;
            $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_caretaker_testresult', $base_condition . ' AND tstamp <' . $start_timestamp, '', 'tstamp DESC', 1);
            if ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
                $row['tstamp'] = $start_timestamp;
                $result = $this->dbrow2instance($row);
                $result_range->addResult($result, 'first');
            }
        }

        // add last value if needed
        $last = $result_range->getLast();
        if ($last && $last->getTimestamp() < $stop_timestamp) {
            if ($graph) {
                $real_last = new TestResult($stop_timestamp, $last->getState(), $last->getValue(), $last->getMessage()->getText(), $last->getSubMessages());
                $result_range->addResult($real_last);
            }
        }

        return $result_range;
    }

    /**
     * Convert DB-Row to Test Node Result
     *
     * @param array $row
     * @return TestResult
     */
    private function dbrow2instance($row)
    {
        $message = new ResultMessage($row['result_msg'], unserialize($row['result_values']));
        $submessages = ($row['result_submessages']) ? unserialize($row['result_submessages']) : array();
        $instance = new TestResult(
            $row['tstamp'],
            $row['result_status'],
            $row['result_value'],
            $message,
            $submessages
        );

        return $instance;
    }

    /**
     * Save the Testresult for the given TestNode
     *
     * @param TestNode $test
     * @param TestResult $testResult
     */
    public function saveTestResultForNode(TestNode $test, $testResult)
    {
        $values = array(
            'test_uid' => $test->getUid(),
            'instance_uid' => $test->getInstance()->getUid(),
            'tstamp' => $testResult->getTimestamp(),
            'result_status' => $testResult->getState(),
            'result_value' => $testResult->getValue(),
            'result_msg' => $testResult->getMessage()->getText(),
            'result_values' => serialize($testResult->getMessage()->getValues()),
            'result_submessages' => serialize($testResult->getSubMessages()),
        );
        $connection = $this->connectionPool->getConnectionForTable('tx_caretaker_testresult');

        $connection->insert('tx_caretaker_testresult', $values);

        // store last results for fast access
        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid', 'tx_caretaker_lasttestresult', 'test_uid = ' . $test->getUid() . ' AND instance_uid = ' . $test->getInstance()->getUid(), '', '', 1);
        if ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
            $GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_caretaker_lasttestresult', 'uid = ' . $row['uid'], $values);
        } else {
            $connection = $this->connectionPool->getConnectionForTable('tx_caretaker_lasttestresult');
            $connection->insert('tx_caretaker_lasttestresult', $values);
        }
    }
}
