<?php


namespace AppBundle\Validator;


class FacebookValidator
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