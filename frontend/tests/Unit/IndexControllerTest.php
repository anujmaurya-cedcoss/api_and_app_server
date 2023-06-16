<?php
declare(strict_types=1);
namespace Tests\Unit;

session_start();

use MyApp\Controllers\IndexController;

class IndexControllerTest extends AbstractUnitTest
{
    public function testsignupAction() {
        $arr = [
            'name' => 'anuj maurya test modified',
            'mail' => 'test@test.com',
            'pass' => 'pass'
        ];

        $user = new IndexController;
        $result = $user->signupAction($arr);
        $this->assertEquals(gettype($result), 'array');
    }
}
