<?php


namespace AppBundle\Validator;


use Symfony\Component\OptionsResolver\OptionsResolver;

class FacebookValidator extends Validator
{
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefined(array(
            'facebookAccessToken'
        ));

        $resolver->setAllowedTypes('facebookAccessToken', 'string');
    }
}