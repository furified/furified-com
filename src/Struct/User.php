<?php
declare(strict_types=1);
namespace Furified\Web\Struct;

use Furified\Web\Engine\Cryptography\{
    Password,
    Symmetric
};
use Furified\Web\Engine\Exceptions\{
    FurifiedException,
    RaceConditionException
};
use Furified\Web\Engine\{
    GlobalConfig,
    Policies\Unique,
    Struct
};
use ParagonIE\HiddenString\HiddenString;
use ParagonIE_Sodium_Core_Util as Util;

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
        'gpgfingerprint' => 'gpgFingerprint',
        'email' => 'email',
        'displayname' => 'displayName'
    ];

    /** @var bool $active */
    protected $active = false;

    /** @var string $email */
    protected $email = '';

    /** @var string $displayName */
    protected $displayName = '';

    /** @var string $gpgFingerprint */
    protected $gpgFingerprint = '';

    /** @var string $username */
    protected $username = '';

    /** @var string $pwHash */
    protected $pwHash = '';

    /** @var string $twoFactorSecret */
    protected $twoFactorSecret;

    /**
     * @param HiddenString $password
     *
     * @return bool
     * @throws FurifiedException
     * @throws \SodiumException
     */
    public function checkPassword(HiddenString $password): bool
    {
        if (!$this->id) {
            throw new RaceConditionException(
                'You cannot set a password until the user record has been saved ' .
                'to the database, in order to prevent race conditions against ' .
                'the sequential primary key.'
            );
        }

        return $this->getPasswordStorage()->verify(
            $password,
            $this->pwHash,
            Util::store64_le($this->id)
        );
    }

    /**
     * @return Password
     * @throws FurifiedException
     */
    protected function getPasswordStorage(): Password
    {
        return new Password(
            (GlobalConfig::instance())->getSymmetricKey()
        );
    }

    /**
     * @return HiddenString
     * @throws FurifiedException
     * @throws \SodiumException
     */
    public function getTwoFactorSecret(): HiddenString
    {
        if (!$this->twoFactorSecret) {
            return new HiddenString('');
        }
        return Symmetric::decryptWithAd(
            $this->twoFactorSecret,
            (GlobalConfig::instance())->getSymmetricKey(),
            'two-factor:' . Util::store64_le($this->id)
        );
    }

    /**
     * @return bool
     */
    public function hasTwoFactorEnabled(): bool
    {
        return isset($this->twoFactorSecret);
    }

    /**
     * @param HiddenString $password
     *
     * @return User
     * @throws FurifiedException
     * @throws \SodiumException
     */
    public function setPassword(HiddenString $password): self
    {
        if (!$this->id) {
            throw new RaceConditionException(
                'You cannot set a password until the user record has been saved ' .
                'to the database, in order to prevent race conditions against ' .
                'the sequential primary key.'
            );
        }

        $this->pwHash = $this->getPasswordStorage()->hash(
            $password,
            Util::store64_le($this->id)
        );
        return $this;
    }

    /**
     * @param HiddenString $hiddenString
     *
     * @return self
     * @throws FurifiedException
     * @throws \SodiumException
     */
    public function setTwoFactorSecret(HiddenString $hiddenString): self
    {
        if (!$this->id) {
            throw new RaceConditionException(
                'You cannot enable 2FA until the user record has been saved ' .
                'to the database, in order to prevent race conditions against ' .
                'the sequential primary key.'
            );
        }

        $this->twoFactorSecret = Symmetric::encryptWithAd(
            $hiddenString,
            (GlobalConfig::instance())->getSymmetricKey(),
            'two-factor:' . Util::store64_le($this->id)
        );
        return $this;
    }
}
