<?php


namespace AppBundle\Validator;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TokenValidator
 *
 * @package AppBundle\Validator
 */
class TokenValidator extends Validator
{
    /**
     * @param OptionsResolver $resolver
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined(array(
            'access_token'
        ));

        $resolver->setRequired(array(
            'access_token'
        ));

        $resolver->setAllowedTypes('access_token', 'string');
    }
}
