<?php


namespace AppBundle\Security\Exception;

/**
 * Class InvalidOffsetException
 *
 * @package AppBundle\Security\Exception
 */
class InvalidOffsetException extends \Exception
{
    public function __construct(\Exception $previous = null)
    {
        parent::__construct('Invalid offset', 102, $previous);
    }
}