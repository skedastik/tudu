<?php

include __DIR__.'/../autoload.php';

use \Tudu\Test\Mock\MockLogger;
use \Tudu\Core\Logger;
use \Tudu\Data\Model\User;

// Inject singleton instances --------------------------------------------------

// do not log during test
Logger::setInstance(new MockLogger());

User::setPasswordDelegate(new \Tudu\Delegate\PHPass());

?>
