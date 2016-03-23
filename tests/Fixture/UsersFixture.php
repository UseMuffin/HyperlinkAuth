<?php
namespace Muffin\HyperlinkAuth\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * Short description for class.
 *
 */
class UsersFixture extends TestFixture
{

    public $table = 'hyperlinkauth_users';

    /**
     * fields property
     *
     * @var array
     */
    public $fields = [
        'id' => ['type' => 'integer'],
        'email' => ['type' => 'string', 'null' => false],
        'token' => ['type' => 'string'],
        'token_expiry' => 'datetime',
        'created' => 'datetime',
        '_constraints' => ['primary' => ['type' => 'primary', 'columns' => ['id']]]
    ];

    /**
     * records property
     *
     * @var array
     */
    public $records = [
        [
            'email' => 'john@doe.com',
            'created' => '2016-03-23 10:00:12',
        ],
    ];
}
