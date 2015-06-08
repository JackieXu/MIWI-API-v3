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
            'username',
            'password'
        ));

        $resolver->setRequired(
            'username',
            'password'
        );

        $resolver->setAllowedTypes('username', 'string');
        $resolver->setAllowedTypes('password', 'string');
    }
}