<?php


namespace AppBundle\Validator;


use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterValidator extends QueryValidator
{
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined(array(
            'interestId'
        ));

        $resolver->setAllowedTypes('interestId', 'numeric');

        $resolver->setDefault('interestId', '0');
    }
}