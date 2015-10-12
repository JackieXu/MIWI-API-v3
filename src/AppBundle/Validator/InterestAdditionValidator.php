<?php


namespace AppBundle\Validator;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class InterestAdditionValidator
 *
 * @package AppBundle\Validator
 */
class InterestAdditionValidator extends Validator
{
    /**
     * Configure options
     *
     * @param OptionsResolver $resolver
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined(array(
            'name'
        ));
    }
}