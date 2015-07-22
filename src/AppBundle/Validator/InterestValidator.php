<?php


namespace AppBundle\Validator;


use Symfony\Component\OptionsResolver\OptionsResolver;

class InterestValidator extends Validator
{
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined(array(
            'name',
            'visibility'
        ));

        $resolver->setRequired(array(
            'name',
        ));

        $resolver->setAllowedTypes('name', 'string');
        $resolver->setAllowedTypes('visibility', 'string');
        $resolver->setDefault('visibility', 'public');
    }
}