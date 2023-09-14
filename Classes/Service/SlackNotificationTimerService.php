<?php

declare(strict_types=1);

namespace Caretaker\Caretaker\Service;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class SlackNotificationTimerService
{
    public const NOTIFICATION_LOG_TABLE = 'tx_caretaker_notification_log';

    public function hasActiveLog(int $nodeUid): bool
    {
        $queryBuilder = $this->getQueryBuilder(self::NOTIFICATION_LOG_TABLE);
        return (bool)$queryBuilder
            ->count('uid')
            ->from(self::NOTIFICATION_LOG_TABLE)->where($queryBuilder->expr()->eq('deleted', 0), $queryBuilder->expr()->eq('node_uid', $queryBuilder->createNamedParameter($nodeUid)))->executeQuery()->fetchColumn();
    }

    public function createLog(int $nodeUid, string $resultHash): void
    {
        $queryBuilder = $this->getQueryBuilder(self::NOTIFICATION_LOG_TABLE);
        $row = [
            'tstamp' => time(),
            'deleted' => 0,
            'node_uid' => $nodeUid,
            'result_hash' => $resultHash
        ];
        $queryBuilder->insert(self::NOTIFICATION_LOG_TABLE)->values($row)->executeStatement();
    }

    public function deleteLog(int $uid): void
    {
        $queryBuilder = $this->getQueryBuilder(self::NOTIFICATION_LOG_TABLE);
        $queryBuilder
            ->update(self::NOTIFICATION_LOG_TABLE)
            ->set('deleted', 1)->where($queryBuilder->expr()->eq('uid', $uid))->executeStatement();
    }

    public function getActiveLog(int $nodeUid): array
    {
        $queryBuilder = $this->getQueryBuilder(self::NOTIFICATION_LOG_TABLE);
        $records = $queryBuilder
            ->select('*')
            ->from(self::NOTIFICATION_LOG_TABLE)->where($queryBuilder->expr()->eq('deleted', 0), $queryBuilder->expr()->eq('node_uid', $queryBuilder->createNamedParameter($nodeUid)))->executeQuery()->fetch();

        return $records;
    }

    private function getQueryBuilder(string $tableName): QueryBuilder
    {
        return GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($tableName);
    }
}
