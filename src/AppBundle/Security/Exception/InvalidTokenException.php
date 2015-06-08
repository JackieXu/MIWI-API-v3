<?php


namespace AppBundle\Security\Exception;


use Symfony\Component\Security\Acl\Exception\Exception;

/**
 * Class InvalidTokenException
 *
 * @package AppBundle\Security\Exception
 */
class InvalidTokenException extends Exception
{
    public function __construct(\Exception $previous = null)
    {
        parent::__construct('Invalid token', 800, $previous);
    }
}