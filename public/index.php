<?php
require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

use App\Model\User;

class Index extends User {
    private $users;

    public function __construct() {
        $this->users = new User();
        $this->index();
    }

    public function index() {
        $users = $this->users->all()->get();
        var_dump($users);
    }
}

new Index();
?>

