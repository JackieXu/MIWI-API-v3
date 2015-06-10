<?php


namespace AppBundle\Validator;


use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class RegistrationValidator
 *
 * @package AppBundle\Validator
 */
class RegistrationValidator extends Validator
{
    /**
     * @param OptionsResolver $resolver
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined(array(
            'email',
            'password',
            'firstName',
            'lastName',
            'birthdate',
        ));

        $resolver->setRequired(array(
            'email',
            'password',
            'firstName',
            'lastName',
            'birthdate'
        ));

        $resolver->setAllowedTypes('email', 'string');
        $resolver->setAllowedTypes('password', 'string');
        $resolver->setAllowedTypes('firstName', 'string');
        $resolver->setAllowedTypes('lastName', 'string');
        $resolver->setAllowedTypes('birthdate', 'string');
    }
}