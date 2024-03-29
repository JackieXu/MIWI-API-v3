<?php


namespace AppBundle\Validator;


use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class LoginValidator
 *
 * @package AppBundle\Validator
 */
class LoginValidator extends EmailValidator
{
    /**
     * @param OptionsResolver $resolver
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefined(array(
            'password'
        ));

        $resolver->setRequired(
            'password'
        );

        $resolver->setAllowedTypes('password', 'string');
    }
}