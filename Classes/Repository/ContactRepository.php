<?php

namespace Caretaker\Caretaker\Repository;

use Caretaker\Caretaker\Constants;
use Caretaker\Caretaker\Entity\Contact\Contact;
use Caretaker\Caretaker\Entity\Contact\ContactRole;
use Caretaker\Caretaker\Entity\Node\AbstractNode;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
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
 * Repository to handle the storing and reconstruction of node contacts
 * aggregatorResults. The whole object <-> database
 * communication happens here.
 *
 * @author Martin Ficzel <martin@work.de>
 * @author Thomas Hempel <thomas@work.de>
 * @author Christopher Hlubek <hlubek@networkteam.com>
 * @author Tobias Liebig <liebig@networkteam.com>
 *
 */
class ContactRepository
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
    public function __construct()
    {
    }

    /**
     * Get the Singleton Object
     *
     * @return ContactRepository
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Get Role Object for given Uid
     *
     * @param <type> $uid
     * @return  ContactRole
     */
    public function getContactRoleByUid($uid)
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable(Constants::table_Roles);
        $rolesRes = $queryBuilder
            ->select('*')
            ->from(Constants::table_Roles)
            ->where(
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($uid, \PDO::PARAM_INT))
            )
            ->executeQuery()
            ->fetchAssociative();

        return ($rolesRes === false) ? false : $this->dbrow2contact_role($rolesRes);
    }

    /**
     * Get Role Object for given String
     *
     * @param string $id
     * @return ContactRole
     */
    public function getContactRoleById($id)
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable(Constants::table_Roles);
        $rolesRes = $queryBuilder
            ->select('*')
            ->from(Constants::table_Roles)
            ->where(
                $queryBuilder->expr()->eq('id', $queryBuilder->createNamedParameter($id))
            )
            ->executeQuery()
            ->fetchAssociative();

        return ($rolesRes === false) ? false : $this->dbrow2contact_role($rolesRes);
    }

    /**
     * Convert dbrow to ContactRole Object
     *
     * @param array $dbrow
     * @return ContactRole
     */
    private function dbrow2contact_role($dbrow)
    {
        $role = new ContactRole($dbrow['uid'], $dbrow['id'], $dbrow['name'], $dbrow['description']);

        return $role;
    }

    /**
     * Get All Contacts for the given node
     *
     * @param AbstractNode $node
     * @return array
     */
    public function getContactsByNode(AbstractNode $node)
    {
        $contacts = array();

        // only Instancegroups and Instances store Contacts
        $nodeType = $node->getType();
        if ($nodeType != Constants::nodeType_Instance && $nodeType != Constants::nodeType_Instancegroup) {
            return $contacts;
        }

        $storageTable = $node->getStorageTable();
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable(Constants::relationTable_Node2Address);
        $res = $queryBuilder
            ->select('*')
            ->from(Constants::relationTable_Node2Address)
            ->where(
                $queryBuilder->expr()->eq('uid_node', $queryBuilder->createNamedParameter($node->getUid(), \PDO::PARAM_INT)),
                $queryBuilder->expr()->eq('node_table', $queryBuilder->createNamedParameter($storageTable))
            )
            ->executeQuery();
        while ($row = $res->fetchAssociative()) {
            if ($contact = $this->dbrow2contact($row)) {
                $contacts[] = $contact;
            }
        }

        return $contacts;
    }

    /**
     * Get All Contacts for the given node that match the given role
     *
     * @param AbstractNode $node
     * @param ContactRole $role
     * @return array
     */
    public function getContactsByNodeAndRole(AbstractNode $node, ContactRole $role)
    {
        $contacts = array();

        // only Instancegroups and Instances store Contacts
        $nodeType = $node->getType();
        if ($nodeType != Constants::nodeType_Instance && $nodeType != Constants::nodeType_Instancegroup) {
            return $contacts;
        }

        $storageTable = $node->getStorageTable();
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable(Constants::relationTable_Node2Address);
        $res = $queryBuilder
            ->select('*')
            ->from(Constants::relationTable_Node2Address)
            ->where(
                $queryBuilder->expr()->eq('uid_node', $queryBuilder->createNamedParameter($node->getUid(), \PDO::PARAM_INT)),
                $queryBuilder->expr()->eq('node_table', $queryBuilder->createNamedParameter($storageTable)),
                $queryBuilder->expr()->eq('role', $queryBuilder->createNamedParameter($role->getUid(), \PDO::PARAM_INT))
            )
            ->executeQuery();
        while ($row = $res->fetchAssociative()) {
            if ($contact = $this->dbrow2contact($row)) {
                $contacts[] = $contact;
            }
        }

        return $contacts;
    }

    /**
     * Convert node address relation record to contact object
     *
     * @parem array $row
     * @param mixed $row
     */
    private function dbrow2contact($row)
    {
        $address = false;
        if ($row['uid_address']) {
            $table = Constants::table_ContactAddresses;
            if (ExtensionManagementUtility::isLoaded('tt_address')) {
                $table = Constants::table_TTAddressAddresses;
            }
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
            $address_row = $queryBuilder
                ->select('*')
                ->from($table)
                ->where(
                    $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($row['uid_address'], \PDO::PARAM_INT))
                )
                ->setMaxResults(1)
                ->executeQuery()
                ->fetchAssociative();
            if ($address_row) {
                $address = $address_row;
            } else {
                return false;
            }
        } else {
            return false;
        }

        if ($row['role']) {
            $role = $this->getContactRoleByUid($row['role']);
        } else {
            $role = false;
        }

        return new Contact($address, $role);
    }
}
