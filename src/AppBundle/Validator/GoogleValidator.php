<?php


namespace AppBundle\Validator;


use Symfony\Component\OptionsResolver\OptionsResolver;

class GoogleValidator extends Validator
{
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefined(array(
            'googleAccessToken'
        ));

        $resolver->setAllowedTypes('googleAccessToken', 'string');
    }
}
