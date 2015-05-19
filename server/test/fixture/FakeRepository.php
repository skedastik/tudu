<?php
namespace Tudu\Test\Fixture;

use \Tudu\Core\Data\Repository\Repository;

final class FakeRepository extends Repository {
    
    public function __construct($db = null) {}
        
    public function getByID($id) {}
    
    public function publicPrenormalize($fakeModel) {
        return parent::prenormalize($fakeModel);
    }
}
?>
