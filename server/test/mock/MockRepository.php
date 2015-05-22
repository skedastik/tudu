<?php
namespace Tudu\Test\Mock;

use \Tudu\Core\Data\Repository;

final class MockRepository extends Repository {
    
    public function __construct($db = null) {}
        
    public function getByID($id) {}
    
    public function publicPrenormalize($mockModel) {
        return parent::prenormalize($mockModel);
    }
}
?>
