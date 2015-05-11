<?php
    include __DIR__.'/../vendor/autoload.php';
    
    function tudu_autoload($className) {
        
        // if class belongs to Tudu...
        if (substr($className, 0, 4) === 'Tudu') {
            
            // replace backslashes w/ forward slashes and strip leading "tudu/"
            $className = substr(str_replace('\\', '/', $className), 5);
            
            // convert everything but class name to lowercase and concat path
            $pos = strrpos($className, '/');
            $path = substr($className, 0, $pos);
            include(__DIR__.'/'.strtolower($path).substr($className, $pos).'.php');
        }
    }
    spl_autoload_register("tudu_autoload");
?>
