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

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use Caretaker\Caretaker\Entity\Node\InstancegroupNode;
use Caretaker\Caretaker\Entity\Node\InstanceNode;
use Caretaker\Caretaker\Entity\Node\TestgroupNode;
use Caretaker\Caretaker\Entity\Node\TestNode;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use Caretaker\Caretaker\Entity\Node\AbstractNode;
use Caretaker\Caretaker\Entity\Node\RootNode;

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
 * Repository to handle  the storing and reconstruction of all
 * caretaker-nodes. The whole object <-> database
 * communication happens here.
 *
 * @author Martin Ficzel <martin@work.de>
 * @author Thomas Hempel <thomas@work.de>
 * @author Christopher Hlubek <hlubek@networkteam.com>
 * @author Tobias Liebig <liebig@networkteam.com>
 *
 */
class NodeRepository
{
    /**
     * Retrieve a specific Node
     *
     * @param bool|int $instancegroupId
     * @param bool|int $instanceId
     * @param bool|int $testgroupId
     * @param bool|int $testId
     * @param bool $show_hidden
     * @return AbstractNode
     */
    public function getNode($instancegroupId = false, $instanceId = false, $testgroupId = false, $testId = false, $show_hidden = false)
    {
        $instancegroupId = (int)$instancegroupId;
        $instanceId = (int)$instanceId;
        $testgroupId = (int)$testgroupId;
        $testId = (int)$testId;

        if ($instancegroupId > 0) {
            $instancegroup = $this->getInstancegroupByUid($instancegroupId, false, $show_hidden);
            if ($instancegroup) {
                return $instancegroup;
            }
        } elseif ($instanceId > 0) {
            $instance = $this->getInstanceByUid($instanceId, false, $show_hidden);
            if ($instance) {
                if ($testgroupId > 0) {
                    // find the instance testgroups
                    $instance_testgroups = $this->getTestgroupsByInstanceUidRecursive($instance->getUid(), $instance, $show_hidden);
                    foreach ($instance_testgroups as $instance_testgroup) {
                        if ($instance_testgroup->getUid() == $testgroupId) {
                            return $instance_testgroup;
                        }
                    }
                } elseif ($testId > 0) {
                    // find directly assigned tests
                    $instance_tests = $this->getTestsByInstanceUid($instance->getUid(), $instance, $show_hidden);
                    foreach ($instance_tests as $instance_test) {
                        if ($instance_test->getUid() == $testId) {
                            return $instance_test;
                        }
                    }
                    // find tests assigned to groups or subgroups
                    $instance_testgroups = $this->getTestgroupsByInstanceUidRecursive($instance->getUid(), $instance, $show_hidden);
                    foreach ($instance_testgroups as $instance_testgroup) {
                        $testgroup_tests = $this->getTestsByGroupUid($instance_testgroup->getUid(), $instance_testgroup, $show_hidden);
                        foreach ($testgroup_tests as $testgroup_test) {
                            if ($testgroup_test->getUid() == $testId) {
                                return $testgroup_test;
                            }
                        }
                    }
                } else {
                    return $instance;
                }
            }
        }

        return false;
    }

    /**
     * Get the Identifier String for a Node
     *
     * @param AbstractNode $node
     * @return string
     */
    public function node2id($node)
    {
        $id = false;
        switch (get_class($node)) {
            case 'InstancegroupNode':
                $id = 'instancegroup_' . $node->getUid();
                break;
            case 'InstanceNode':
                $id = 'instance_' . $node->getUid();
                break;
            case 'TestgroupNode':
                $instance = $node->getInstance();
                $id = 'instance_' . $instance->getUid() . '_testgroup_' . $node->getUid();
                break;
            case 'TestNode':
                $instance = $node->getInstance();
                $id = 'instance_' . $instance->getUid() . '_test_' . $node->getUid();
                break;
            case 'RootNode':
                $instance = $node->getInstance();
                $id = 'root';
                break;
        }

        return $id;
    }

    /**
     * Get the Node Object for a given Identifier String
     *
     * @param string $id_string
     * @param bool $show_hidden
     * @return AbstractNode
     */
    public function id2node($id_string, $show_hidden = false)
    {
        if ($id_string == 'root') {
            return $this->getRootNode();
        }

        $parts = explode('_', $id_string);
        $info = array();
        for ($i = 0; $i < count($parts); $i += 2) {
            switch ($parts[$i]) {
                case 'instancegroup':
                    $info['instancegroup'] = (int)$parts[$i + 1];
                    break;
                case 'instance':
                    $info['instance'] = (int)$parts[$i + 1];
                    break;
                case 'testgroup':
                    $info['testgroup'] = (int)$parts[$i + 1];
                    break;
                case 'test':
                    $info['test'] = (int)$parts[$i + 1];
                    break;
            }
        }

        return $this->getNode($info['instancegroup'], $info['instance'], $info['testgroup'], $info['test'], $show_hidden);
    }

    /**
     * Singleton Instance
     *
     * @var NodeRepository
     */
    private static $instance = null;

    /**
     * Constructor
     */
    public function __construct()
    {
    }

    /**
     * Get Singleton Instance
     */
    public static function getInstance(): ?NodeRepository
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Get a Rootnode Object
     *
     * @return RootNode
     */
    public function getRootNode()
    {
        return new RootNode();
    }

    /*
     * Methods for Instancegroup Access
     */

    /**
     * Get all Instancegroups
     *
     * @param bool|AbstractNode $parent
     * @param bool $show_hidden
     * @return array
     */
    public function getAllInstancegroups($parent = false, $show_hidden = false)
    {
        $hidden = '';
        if (!$show_hidden) {
            $hidden = ' AND hidden=0 ';
        }
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_caretaker_instancegroup');
        $queryBuilder->select('*')
            ->from('tx_caretaker_instancegroup')
            ->where(
                $queryBuilder->expr()->eq('deleted', 0)
            );

        if ($hidden) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->eq('hidden', $hidden)
            );
        }

        $resultRows = $queryBuilder->executeQuery();
        $result = [];

        while ($row = $resultRows->fetchAssociative()) {
            $item = $this->dbrow2instancegroup($row, $parent);
            if ($item) {
                $result[] = $item;
            }
        }

        return $result;
    }

    /**
     * Get the instancegroup with UID
     *
     * @param $uid
     * @param $parent
     * @param $show_hidden
     * @return bool|InstancegroupNode
     */
    public function getInstancegroupByUid($uid, $parent = false, $show_hidden = false)
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_caretaker_instancegroup');
        $queryBuilder->select('*')
            ->from('tx_caretaker_instancegroup')
            ->where(
                $queryBuilder->expr()->eq('deleted', 0),
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter((int)$uid, \PDO::PARAM_INT))
            );

        if (!$show_hidden) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->eq('hidden', 0)
            );
        }

        $row = $queryBuilder->executeQuery()->fetchAssociative();
        if ($row) {
            return $this->dbrow2instancegroup($row, $parent);
        }
        return false;
    }

    /**
     * Get all Instancegroups which are Children of Instancegroup with UID xxx
     *
     * @param int $parent_group_uid
     * @param AbstractNode $parent
     * @param bool $show_hidden
     * @return array
     */
    public function getInstancegroupsByParentGroupUid($parent_group_uid, $parent, $show_hidden = false)
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_caretaker_instancegroup');
        if ($show_hidden) {
            $queryBuilder->getRestrictions()
                ->removeByType(HiddenRestriction::class);
        }
        $resultRows = $queryBuilder
            ->select('*')
            ->from('tx_caretaker_instancegroup')
            ->where(
                $queryBuilder->expr()->eq('parent_group', $queryBuilder->createNamedParameter((int)$parent_group_uid, \PDO::PARAM_INT))
            )
            ->groupBy('title')
            ->executeQuery();

        while ($row = $resultRows->fetchAssociative()) {
            $item = $this->dbrow2instancegroup($row, $parent);
            if ($item) {
                $result[] = $item;
            }
        }

        return $result;
    }

    /**
     * Get the Instancegroup wich is the prarent of Instancegroup X
     *
     * @param int $child_group_uid
     * @param bool $show_hidden
     * @return InstancegroupNode
     */
    public function getInstancegroupByChildGroupUid($child_group_uid, $show_hidden = false)
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_caretaker_instancegroup');
        $queryBuilder->select('parent_group')
            ->from('tx_caretaker_instancegroup')
            ->where(
                $queryBuilder->expr()->eq('deleted', 0),
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter((int)$child_group_uid, \PDO::PARAM_INT))
            );

        if (!$show_hidden) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->eq('hidden', 0)
            );
        }

        $row = $queryBuilder->executeQuery()->fetchAssociative();
        if ($row) {
            $parent_item = $this->getInstancegroupByUid($row['parent_group']);

            return $parent_item;
        }

        return false;
    }

    /**
     * Convert Instancegroup DB-Row to Instancegroup-Object
     *
     * @param array $row
     * @param AbstractNode $parent
     * @return InstancegroupNode
     */
    private function dbrow2instancegroup($row, $parent)
    {
        // check access
        if (ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isFrontend()) {
            if ($GLOBALS['TSFE']->sys_page) {
                $result = $GLOBALS['TSFE']->sys_page->checkRecord('tx_caretaker_instancegroup', $row['uid']);
            } else {
                // this has to be implemented here
                $result = true;
            }

            if (!$result) {
                return false;
            }
        }

        // find parent node if it was not already handed over
        if ($parent == false) {
            if (intval($row['parent_group']) > 0) {
                $parent = $this->getInstancegroupByUid($row['parent_group'], false);
            } else {
                $parent = $this->getRootNode();
            }
        }

        // create instance
        $instance = new InstancegroupNode($row['uid'], $row['title'], $parent, $row['hidden']);
        if ($row['description']) {
            $instance->setDescription($row['description']);
        }
        $instance->setDbRow($row);

        return $instance;
    }

    /*
     * Methods for Instance Access
     */

    /**
     * Get all Instances in Repository
     *
     * @param bool|AbstractNode $parent
     * @param bool $show_hidden
     * @return array
     */
    public function getAllInstances($parent = false, $show_hidden = false)
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_caretaker_instance');
        $queryBuilder->select('*')
            ->from('tx_caretaker_instance')
            ->where(
                $queryBuilder->expr()->eq('deleted', 0)
            );

        if (!$show_hidden) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->eq('hidden', 0)
            );
        }

        $resultRows = $queryBuilder->executeQuery();
        $result = [];

        while ($row = $resultRows->fetchAssociative()) {
            $item = $this->dbrow2instance($row, $parent);
            if ($item) {
                $result[] = $item;
            }
        }

        return $result;
    }

    /**
     * Get Instance with UID X
     *
     * @param int $uid
     * @param $parent
     * @param $show_hidden
     * @return bool|InstanceNode
     */
    public function getInstanceByUid($uid, $parent = null, $show_hidden = false)
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_caretaker_instance');
        $queryBuilder->select('*')
            ->from('tx_caretaker_instance')
            ->where(
                $queryBuilder->expr()->eq('deleted', 0),
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter((int)$uid, \PDO::PARAM_INT))
            );

        if (!$show_hidden) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->eq('hidden', 0)
            );
        }

        $row = $queryBuilder->executeQuery()->fetchAssociative();
        if ($row) {
            return $this->dbrow2instance($row, $parent);
        }
        return false;
    }

    /**
     * Get all Instances which are part of Group X
     *
     * @param int $uid
     * @param AbstractNode $parent
     * @param bool $show_hidden
     * @return array
     */
    public function getInstancesByInstancegroupUid($uid, $parent = null, $show_hidden = false): array
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_caretaker_instance');
        $queryBuilder->select('uid')
            ->from('tx_caretaker_instance')
            ->where(
                $queryBuilder->expr()->eq('instancegroup', $queryBuilder->createNamedParameter((int)$uid, \PDO::PARAM_INT))
            )
            ->orderBy('title');

        $resultRows = $queryBuilder->executeQuery();
        $result = [];

        while ($row = $resultRows->fetchAssociative()) {
            $item = $this->getInstanceByUid($row['uid'], $parent, $show_hidden);
            if ($item) {
                $result[] = $item;
            }
        }

        return $result;
    }

    /**
     * Convert DB-Row to Instance-Object
     *
     * @param array $row
     * @param AbstractNode $parent
     * @return InstanceNode
     */
    private function dbrow2instance($row, $parent = null)
    {
        // check access
        if (ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isFrontend()) {
            if ($GLOBALS['TSFE']->sys_page) {
                $result = $GLOBALS['TSFE']->sys_page->checkRecord('tx_caretaker_instance', $row['uid']);
            } else {
                // implement check in eID mode here
                $result = true;
            }
            if (!$result) {
                return false;
            }
        }

        // find parent node if it was not already handed over
        if (!$parent) {
            if (intval($row['instancegroup']) > 0) {
                $parent = $this->getInstancegroupByUid($row['instancegroup'], false);
            } else {
                $parent = $this->getRootNode();
            }
        }
        // create Node
        $instance = new InstanceNode($row['uid'], $row['title'], $parent, $row['url'], $row['host'], $row['public_key'], $row['hidden']);
        if ($row['description']) {
            $instance->setDescription($row['description']);
        }
        $extConfig = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('caretaker');
        $newConfigurationOverrideEnabled = $extConfig['features.']['newConfigurationOverrides.']['enabled'] == '1';
        if (VersionNumberUtility::convertVersionNumberToInteger(VersionNumberUtility::getCurrentTypo3Version()) >= VersionNumberUtility::convertVersionNumberToInteger('7.5.0')) {
            // enable new configurations overrides automatically with 7.5 and later
            $newConfigurationOverrideEnabled = true;
        }
        if ($newConfigurationOverrideEnabled) {
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_caretaker_instance_override');
            $configurationOverrides = $queryBuilder
                ->select('*')
                ->from('tx_caretaker_instance_override')
                ->where(
                    $queryBuilder->expr()->eq('type', $queryBuilder->createNamedParameter('test_configuration')),
                    $queryBuilder->expr()->eq('instance', $queryBuilder->createNamedParameter((int)$row['uid'], \PDO::PARAM_INT)),
                    $queryBuilder->expr()->eq('deleted', 0)
                )
                ->executeQuery()
                ->fetchAllAssociative();

            if (is_array($configurationOverrides) && count($configurationOverrides) > 0) {
                $instance->setTestConfigurations($configurationOverrides);
            }

            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_caretaker_instance_override');
            $curlOptions = $queryBuilder
                ->select('*')
                ->from('tx_caretaker_instance_override')
                ->where(
                    $queryBuilder->expr()->eq('type', $queryBuilder->createNamedParameter('curl_option')),
                    $queryBuilder->expr()->eq('instance', $queryBuilder->createNamedParameter((int)$row['uid'], \PDO::PARAM_INT)),
                    $queryBuilder->expr()->eq('deleted', 0)
                )
                ->executeQuery()
                ->fetchAllAssociative();

            if (is_array($curlOptions) && count($curlOptions) > 0) {
                $instance->setCurlOptions($curlOptions);
            }
        } else {
            if ($row['testconfigurations']) {
                $instance->setTestConfigurations($row['testconfigurations']);
            }
        }
        $instance->setDbRow($row);
    }

    /*
     * Methods for Testgroup Access
     */

    /**
     * Get all Testgroups
     *
     * @param bool|AbstractNode $parent
     * @param bool $show_hidden
     * @return array
     */
    public function getAllTestgroups($parent = false, $show_hidden = false)
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_caretaker_testgroup');
        $queryBuilder->select('*')
            ->from('tx_caretaker_testgroup')
            ->where(
                $queryBuilder->expr()->eq('deleted', 0)
            );

        if (!$show_hidden) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->eq('hidden', 0)
            );
        }

        $resultRows = $queryBuilder->executeQuery();
        $result = [];

        while ($row = $resultRows->fetchAssociative()) {
            $result[] = $this->dbrow2testgroup($row, $parent);
        }

        return $result;
    }

    /**
     * Get all Testgroups of Instance X
     *
     * @param int $instanceId
     * @param bool|AbstractNode $parent
     * @param bool $show_hidden
     * @return array
     */
    public function getTestgroupsByInstanceUid($instanceId, $parent = false, $show_hidden = false)
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_caretaker_instance_testgroup_mm');
        $queryBuilder->select('uid_foreign')
            ->from('tx_caretaker_instance_testgroup_mm')
            ->where(
                $queryBuilder->expr()->eq('uid_local', $queryBuilder->createNamedParameter((int)$instanceId, \PDO::PARAM_INT))
            )
            ->orderBy('sorting');

        $resultRows = $queryBuilder->executeQuery();
        $instance_group_ids = [];

        while ($row = $resultRows->fetchAssociative()) {
            $instance_group_ids[] = $row['uid_foreign'];
        }

        $result = [];
        foreach ($instance_group_ids as $id) {
            $item = $this->getTestgroupByUid($id, $parent, $show_hidden);
            if ($item) {
                $result[] = $item;
            }
        }

        return $result;
    }

    /**
     * Get all Testgroups of Instance X all subgroups are included recursively
     *
     * @param int $instanceId
     * @param bool|AbstractNode $parent
     * @param bool $show_hidden
     * @return array
     */
    public function getTestgroupsByInstanceUidRecursive($instanceId, $parent = false, $show_hidden = false)
    {
        // direct assigned results
        $testgroups = $this->getTestgroupsByInstanceUid($instanceId, $parent, $show_hidden);
        // include subresults
        /** @var TestgroupNode $testgroup */
        foreach ($testgroups as $testgroup) {
            $subgroups = $this->getTestgroupsByParentGroupUidRecursive($testgroup->getUid(), $testgroup, $show_hidden);
            $testgroups = array_merge($testgroups, $subgroups);
        }

        return $testgroups;
    }

    /**
     * Get Testgroup of UID X
     *
     * @param int $uid
     * @param bool|AbstractNode $parent
     * @param bool $show_hidden
     * @return array
     */
    public function getTestgroupByUid($uid, $parent = false, $show_hidden = false)
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_caretaker_testgroup');
        $queryBuilder->select('*')
            ->from('tx_caretaker_testgroup')
            ->where(
                $queryBuilder->expr()->eq('deleted', 0),
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter((int)$uid, \PDO::PARAM_INT))
            )
            ->orderBy('sorting_foreign');

        if (!$show_hidden) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->eq('hidden', 0)
            );
        }

        $resultRows = $queryBuilder->executeQuery();
        $result = [];

        while ($row = $resultRows->fetchAssociative()) {
            $result[] = $this->dbrow2testgroup($row, $parent);
        }

        return $result;
    }

    /**
     * Get all Testgroups wich are child of Testgroup X
     *
     * @param int $parent_group_uid
     * @param AbstractNode $parent
     * @param bool $show_hidden
     * @return array
     */
    public function getTestgroupsByParentGroupUid($parent_group_uid, $parent, $show_hidden)
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_caretaker_testgroup');
        $queryBuilder->select('*')
            ->from('tx_caretaker_testgroup')
            ->where(
                $queryBuilder->expr()->eq('deleted', 0),
                $queryBuilder->expr()->eq('parent_group', $queryBuilder->createNamedParameter((int)$parent_group_uid, \PDO::PARAM_INT))
            );

        if (!$show_hidden) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->eq('hidden', 0)
            );
        }

        $resultRows = $queryBuilder->executeQuery();
        $result = [];

        while ($row = $resultRows->fetchAssociative()) {
            $result[] = $this->dbrow2testgroup($row, $parent);
        }

        return $result;
    }

    /**
     * Get all Testgroups of Instance X all subgroups are included recursively
     *
     * @param int $groupId
     * @param bool|AbstractNode $parent
     * @param bool $show_hidden
     * @return array
     */
    public function getTestgroupsByParentGroupUidRecursive($groupId, $parent = false, $show_hidden = false)
    {
        // direct assigned results
        $testgroups = $this->getTestgroupsByParentGroupUid($groupId, $parent, $show_hidden);
        // include subresults
        /** @var TestgroupNode $testgroup */
        foreach ($testgroups as $testgroup) {
            $subgroups = $this->getTestgroupsByParentGroupUidRecursive($testgroup->getUid(), $testgroup, $show_hidden);
            $testgroups = array_merge($testgroups, $subgroups);
        }

        return $testgroups;
    }

    /**
     * Convert Testgroup DB-Record to Object
     *
     * @param array $row
     * @param AbstractNode $parent
     * @return TestgroupNode
     */
    private function dbrow2testgroup($row, $parent)
    {
        $instance = new TestgroupNode($row['uid'], $row['title'], $parent, $row['hidden']);
        if ($row['description']) {
            $instance->setDescription($row['description']);
        }
        $instance->setDbRow($row);

        return $instance;
    }

    /*
     * Methods for Test Access
     */

    /**
     * Get Tests of Group X
     *
     * @param int $group_id
     * @param bool|AbstractNode $parent
     * @param bool $show_hidden
     * @return array
     */
    public function getTestsByGroupUid($group_id, $parent = false, $show_hidden = false)
    {
        $ids = array();

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_caretaker_testgroup_test_mm');
        $queryBuilder->select('uid_local')
            ->from('tx_caretaker_testgroup_test_mm')
            ->where(
                $queryBuilder->expr()->eq('uid_foreign',  $queryBuilder->createNamedParameter((int)$group_id, \PDO::PARAM_INT))
            )
            ->orderBy('sorting_foreign');


        $resultRows = $queryBuilder->executeQuery();
        while ($row = $resultRows->fetchAssociative()) {
            $ids[] = $row['uid_local'];
        }

        $tests = array();
        foreach ($ids as $uid) {
            $item = $this->getTestByUid($uid, $parent, $show_hidden);
            if ($item) {
                $tests[] = $item;
            }
        }

        return $tests;
    }

    /**
     * Get Tests of Instance X
     *
     * @param int $instance_id
     * @param bool|AbstractNode $parent
     * @param bool $show_hidden
     * @return array
     */
    public function getTestsByInstanceUid($instance_id, $parent = false, $show_hidden = false): array
    {
        $ids = array();

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_caretaker_instance_test_mm');
        $queryBuilder->select('uid_local')
            ->from('tx_caretaker_instance_test_mm')
            ->where(
                $queryBuilder->expr()->eq('uid_foreign',  $queryBuilder->createNamedParameter((int)$instance_id, \PDO::PARAM_INT))
            );


        $resultRows = $queryBuilder->executeQuery();
        while ($row = $resultRows->fetchAssociative()) {
            $ids[] = $row['uid_local'];
        }

        $tests = array();
        foreach ($ids as $uid) {
            $item = $this->getTestByUid($uid, $parent, $show_hidden);
            if ($item) {
                $tests[] = $item;
            }
        }

        return $tests;
    }

    /**
     * Get Test of UID X
     *
     * @param int $uid
     * @param bool|AbstractNode $parent
     * @param bool $show_hidden
     * @return bool|TestNode
     */
    public function getTestByUid($uid, $parent = false, $show_hidden = false)
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_caretaker_test');
        $queryBuilder->select('*')
            ->addSelectLiteral('(
        SELECT GROUP_CONCAT(r.id)
        FROM tx_caretaker_roles r, tx_caretaker_test_roles_mm mm
        WHERE mm.uid_local = tx_caretaker_test.uid
        AND r.uid = mm.uid_foreign
        ) as roles_ids')
            ->from('tx_caretaker_test')
            ->where(
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq('deleted', 0),
                    $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter((int)$uid, PDO::PARAM_INT))
                )
            );

        if (!$show_hidden) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->eq('hidden', 0)
            );
        }
        $resultRows = $queryBuilder->execute();

        while ($row = $resultRows->fetchAssociative()) {
            $test = $this->dbrow2test($row, $parent);
            // the test may be disabled/hidden by configuration, so we need to double-check the hidden state
            if (!$show_hidden && $test->getHidden()) {
                return false;
            }

            return $test;
        }

        return false;
    }

    /**
     * Convert Test DB-Row to Object
     *
     * @param array $row
     * @param bool|AbstractNode $parent
     * @return TestNode
     */
    private function dbrow2test($row, $parent = false)
    {
        if (!$parent) {
            return false;
        }

        $test = new TestNode(
            $row['uid'],
            $row['title'],
            $parent,
            $row['test_service'],
            $row['test_conf'],
            $row['test_interval'],
            $row['test_retry'],
            $row['test_due'],
            $row['test_interval_start_hour'],
            $row['test_interval_stop_hour'],
            $row['hidden'],
            $row['roles_ids']
        );
        if ($row['description']) {
            $test->setDescription($row['description']);
        }
        $test->setDbRow($row);

        return $test;
    }
}
