<?php


namespace AppBundle\Security\Exception;

/**
 * Class InvalidInterestException
 *
 * @package AppBundle\Security\Exception
 */
class InvalidInterestException extends \Exception
{
    public function __construct(\Exception $previous = null)
    {
        parent::__construct('Invalid interest', 901, $previous);
    }
}
