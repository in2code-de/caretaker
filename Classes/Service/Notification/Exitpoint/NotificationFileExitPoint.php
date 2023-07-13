<?php

namespace Caretaker\Caretaker\Service\Notification\Exitpoint;

use Caretaker\Caretaker\Entity\Node\AbstractNode;
use Caretaker\Caretaker\Entity\Result\TestResult;

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
class NotificationFileExitPoint extends NotificationBaseExitPoint
{
    /**
     * @param array $notification
     * @param array $overrideConfig
     * @return void
     */
    public function addNotification($notification, $overrideConfig)
    {
        $config = $this->getConfig($overrideConfig);

        $line = implode(
                ' ',
                array(
                    date('Y-m-d H:i:s'),
                    ($notification['node'] instanceof AbstractNode ? $notification['node']->getInstance()->getTitle() : '-'),
                    ($notification['node'] instanceof AbstractNode ? '[' . $notification['node']->getCaretakerNodeId() . ']' : '-'),
                    $notification['node']->getTitle(),
                    ($notification['lastResult'] instanceof TestResult ? $notification['lastResult']->getLocallizedStateInfo() : '-'),
                    '->',
                    ($notification['result'] instanceof TestResult ? $notification['result']->getLocallizedStateInfo() : '-'),
                )
            ) . chr(10);

        $fileHandle = fopen($config['filePath'], 'a');
        fwrite($fileHandle, $line);
        fclose($fileHandle);
    }

    /**
     * @return void
     */
    public function execute()
    {
        // nothing to do here
    }
}
