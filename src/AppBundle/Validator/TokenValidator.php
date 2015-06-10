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
            'accessToken'
        ));

        $resolver->setRequired(array(
            'accessToken'
        ));

        $resolver->setAllowedTypes('accessToken', 'string');
    }
}
