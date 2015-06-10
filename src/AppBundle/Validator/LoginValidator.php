<?php


namespace AppBundle\Validator;


use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class LoginValidator
 *
 * @package AppBundle\Validator
 */
class LoginValidator extends Validator
{
    /**
     * @param OptionsResolver $resolver
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined(array(
            'email',
            'password'
        ));

        $resolver->setRequired(
            'email',
            'password'
        );

        $resolver->setAllowedTypes('email', 'string');
        $resolver->setAllowedTypes('password', 'string');
    }
}