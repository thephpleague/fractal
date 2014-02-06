<?php

/*
 * This file is part of the Silex framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Silex\Tests\Provider;

class SpoolStub implements \Swift_Spool
{
    private $messages = array();
    public $hasFlushed = false;

    public function getMessages()
    {
        return $this->messages;
    }

    public function start()
    {
    }

    public function stop()
    {
    }

    public function isStarted()
    {
        return count($this->messages) > 0;
    }

    public function queueMessage(\Swift_Mime_Message $message)
    {
        $this->messages[] = $message;
    }

    public function flushQueue(\Swift_Transport $transport, &$failedRecipients = null)
    {
        $this->hasFlushed = true;
        $this->messages = array();
    }
}
