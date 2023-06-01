<?php
namespace Caretaker\Caretaker\UserFunc;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class LabelUserFunc
{
    public function getLabel(&$incomingParameters)
    {
        $table = $incomingParameters['table'];
        $row = $incomingParameters['row'];
        $titleParts = array();

        switch ($table) {
            case 'tx_caretaker_instance_override':
                $tableTitle = '';
                $type = $row['type'];
                if (is_array($type) && count($type) > 0) {
                    $type = $type[0];
                }
                foreach ($GLOBALS['TCA'][$table]['columns']['type']['config']['items'] as $item) {
                    if ($item['value'] == $type) {
                        $tableTitle = $item['label'];
                    }
                }
                if (substr($tableTitle, 0, 4) === 'LLL:') {
                    $tableTitle = $GLOBALS['LANG']->sL($tableTitle);
                }
                $titleParts[] = $tableTitle;
                if ($type == 'test_configuration') {
                    $test = $row['test'];
                    if (is_array($test) && count($test) > 0) {
                        $test = array_shift($test);
                    }

                    $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_caretaker_test');
                    $testRecord = $queryBuilder->select('title')
                        ->from('tx_caretaker_test')
                        ->where(
                            $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($test, \PDO::PARAM_INT))
                        );

                    $titleParts[] = $testRecord['title'];
                } elseif ($type == 'curl_option') {
                    $curlOption = $row['curl_option'];
                    if (is_array($curlOption) && count($curlOption) > 0) {
                        $curlOption = array_shift($curlOption);
                    }
                    $titleParts[] = $curlOption;
                }
                break;
        }

        $incomingParameters['title'] = implode(', ', $titleParts);
    }
}
