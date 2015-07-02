<?php


namespace AppBundle\Validator;


use Symfony\Component\OptionsResolver\OptionsResolver;

class EmailValidator extends Validator
{
    /**
     * @param OptionsResolver $resolver
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined(array(
            'email'
        ));

        $resolver->setRequired(array(
            'email'
        ));

        $resolver->setAllowedTypes('email', 'string');
    }
}