<?php
namespace Tudu\Test\Mock;

use \Tudu\Core\Data\Model;
use \Tudu\Core\Data\Repository;

final class MockRepository extends Repository {
    
    public function fetch(Model $mockModel) {
        $this->normalize($mockModel);
        return $mockModel;
    }
}

?>
