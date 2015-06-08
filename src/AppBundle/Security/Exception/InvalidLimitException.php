<?php


namespace AppBundle\Security\Exception;

/**
 * Class InvalidLimitException
 *
 * @package AppBundle\Security\Exception
 */
class InvalidLimitException extends \Exception
{
    public function __construct(\Exception $previous = null)
    {
        parent::__construct('Invalid limit', 101, $previous);
    }
}
