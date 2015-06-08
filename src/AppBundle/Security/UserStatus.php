<?php


namespace AppBundle\Security;

/**
 * Class UserStatus
 *
 * @package AppBundle\Security
 */
final class UserStatus
{
    const DEACTIVATED           = -1;
    const REGISTERED            = 0;
    const HAS_ACCEPTED_TERMS    = 1;
    const HAS_INTERESTS         = 2;
    const HAS_SHARED            = 3;
}