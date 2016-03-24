<?php
namespace Muffin\HyperlinkAuth\Auth;

use Cake\Auth\FormAuthenticate;
use Cake\Controller\ComponentRegistry;
use Cake\Network\Request;
use Cake\Network\Response;
use Cake\ORM\TableRegistry;
use Cake\Utility\Security;

class HyperlinkAuthenticate extends FormAuthenticate
{
    protected $_defaultConfig = [
        'token' => [
            'parameter' => 'token',
            'detector' => 'token',
            'length' => 10,
            'expires' => '+10 mins',
            'finder' => null,
            'factory' => null,
        ],
        'fields' => [
            'username' => 'email',
            'token' => 'token',
            'expires' => 'token_expiry',
        ],
        'userModel' => 'Users',
        'scope' => [],
        'finder' => 'all',
        'contain' => null,
    ];

    public function __construct(ComponentRegistry $registry, array $config = [])
    {
        parent::__construct($registry, $config);

        Request::addDetector($this->config('token.detector'), function(Request $request) {
            return (bool)$request->query($this->config('token.parameter'))
                || (bool)$request->param($this->config('token.parameter'));
        });

        if (!$this->config('token.factory')) {
            $this->config('token.factory', [$this, '_tokenize']);
        }
    }

    public function authenticate(Request $request, Response $response)
    {
        $config = $this->config();

        if (!$request->is($config['token']['detector'])) {
            return $this->_findUser($request->data[$this->config('fields.username')]);
        }

        $token = $request->param($config['token']['parameter']);
        if (!$token) {
            $token = $request->query($config['token']['parameter']);
        }

        if ($finder = $this->config('token.finder')) {
            return call_user_func($finder, $token);
        }

        $this->config('fields.username', $this->config('fields.token'));
        return $this->_findUser($token);
    }

    public function token(array $user)
    {
        return call_user_func($this->config('token.factory'), $user);
    }

    protected function _tokenize(array $user)
    {
        $config = $this->_config;
        $fields = $config['fields'];
        $table = TableRegistry::get($config['userModel']);
        $conditions = [$fields['username'] => $user[$fields['username']]];
        $data = [
            $fields['token'] => Security::randomBytes($config['token']['length']),
            $fields['expires'] => new \DateTime($config['token']['expires']),
        ];
        $table->updateAll($data, $conditions);
        return $data[$fields['token']];
    }
}
