<?php
namespace Muffin\HyperlinkAuth\Test\Auth;

use Cake\Network\Request;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Muffin\HyperlinkAuth\Auth\HyperlinkAuthenticate;

class HyperlinkAuthenticateTest extends TestCase
{

    public $fixtures = ['plugin.Muffin/HyperlinkAuth.Users'];

    public function setUp()
    {
        parent::setUp();
        TableRegistry::clear();
        $this->collection = $this->getMock('Cake\Controller\ComponentRegistry');
        $this->response = $this->getMock('Cake\Network\Response');
        $this->auth = $this->getMock(
            'Muffin\HyperlinkAuth\Auth\HyperlinkAuthenticate',
            ['_findUser'],
            [$this->collection]
        );
    }

    public function testConstructor()
    {
        $request = new Request();
        $request->query['t'] = 'some_token';

        $auth = new HyperlinkAuthenticate($this->collection, [
            'token' => [
                'parameter' => 't',
                'detector' => 'token_login'
            ],
        ]);

        $request->query['t'] = 'some_token';

        $this->assertEquals('token_login', $auth->config('token.detector'));
        $this->assertNotNull($auth->config('token.factory'));
        $this->assertTrue($request->is('token_login'));
    }

    public function testAuthenticate()
    {
        $email = 'john@doe.com';
        $request = new Request(['post' => compact('email')]);

        $this->auth->expects($this->once())
            ->method('_findUser')
            ->with($email);

        $this->auth->authenticate($request, $this->response);
    }

    public function testAuthenticateToken()
    {
        $token = 'some_token';
        $request = new Request(['query' => compact('token')]);

        $this->auth->expects($this->once())
            ->method('_findUser')
            ->with($token);

        $this->auth->authenticate($request, $this->response);
    }

    public function testAuthenticateTokenWithCustomFinder()
    {
        $token = 'some_token';
        $request = new Request(['query' => compact('token')]);

        $auth = new HyperlinkAuthenticate($this->collection, [
            'token' => ['finder' => function($t) use ($token) {
                $this->assertEquals($token, $t);
            }]
        ]);

        $auth->authenticate($request, $this->response);
    }

    public function testToken()
    {
        $users = TableRegistry::get('Users', ['table' => 'hyperlinkauth_users']);
        $token = $this->auth->token(['id' => 1, 'email' => 'john@doe.com']);
        $this->assertNotEmpty($token);
        $this->assertTrue($users->exists(compact('token')));
    }
}
