<?php


namespace AppBundle\Validator;


use Symfony\Component\OptionsResolver\OptionsResolver;

class FavoriteValidator extends Validator
{
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefined(array(
            'itemId'
        ));

        $resolver->setAllowedTypes('itemId', 'numeric');
    }
}