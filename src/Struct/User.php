<?php
declare(strict_types=1);
namespace Furified\Web\Struct;

use Furified\Web\Engine\Policies\Unique;
use Furified\Web\Engine\Struct;

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
        'fullname' => 'fullName',
        'chronicle' => 'chronicle'
    ];


}
