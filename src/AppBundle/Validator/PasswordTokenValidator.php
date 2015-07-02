<?php


namespace AppBundle\Validator;


use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class PasswordTokenValidator
 *
 * @package AppBundle\Validator
 */
class PasswordTokenValidator extends Validator
{
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefined(array(
            'password',
            'token'
        ));

        $resolver->setRequired(array(
            'password',
            'token'
        ));

        $resolver->setAllowedTypes('password', 'string');
        $resolver->setAllowedTypes('token', 'string');
    }
}