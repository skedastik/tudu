<?php

include __DIR__.'/../tudu_autoload.php';

use \Tudu\Test\Mock\MockLogger;
use \Tudu\Core\Logger;

// do not log during test
Logger::setInstance(new MockLogger());

?>