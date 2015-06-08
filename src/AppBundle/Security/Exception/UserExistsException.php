<?php


namespace AppBundle\Security\Exception;

/**
 * Class UserExistsException
 *
 * @package AppBundle\Security\Exception
 */
class UserExistsException extends \Exception
{
    public function __construct(\Exception $previous = null)
    {
        parent::__construct('User already exists with that e-mail address', 801, $previous);
    }
}