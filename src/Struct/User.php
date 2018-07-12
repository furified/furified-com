<?php
declare(strict_types=1);
namespace Furified\Web\Struct;

use Furified\Web\Engine\Policies\Unique;
use Furified\Web\Engine\Struct;
use ParagonIE\HiddenString\HiddenString;

/**
 * Class User
 * @package Furified\Web\Struct
 */
class User extends Struct implements Unique
{
    const TABLE_NAME = 'furified_users';
    const PRIMARY_KEY = 'userid';
    const DB_FIELD_NAMES = [
        'userid' => 'id',
        'active' => 'active',
        'username' => 'username',
        'pwhash' => 'pwHash',
        'twofactor' => 'twoFactorSecret',
        'email' => 'email',
        'fullname' => 'fullName'
    ];

    /** @var bool $active */
    protected $active = false;

    /** @var string $email */
    protected $email = '';

    /** @var string $fullName */
    protected $fullName = '';

    /** @var string $username */
    protected $username = '';

    /** @var string $pwHash */
    protected $pwHash = '';

    /** @var HiddenString $twoFactorSecret */
    protected $twoFactorSecret;

}
