<?php


namespace AppBundle\Validator;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class InterestArrayValidator
 *
 * @package AppBundle\Validator
 */
class InterestArrayValidator extends Validator
{
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined(
            'interests'
        );

        $resolver->setRequired(array(
            'interests'
        ));

        $resolver->setAllowedTypes('interests', 'array');
    }
}