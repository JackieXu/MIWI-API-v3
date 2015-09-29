<?php


namespace AppBundle\Validator;


use Symfony\Component\OptionsResolver\OptionsResolver;

class ItemValidator extends Validator
{
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined(array(
            'title',
            'body',
            'interestId',
            'userId',
            'images'
        ));

        $resolver->setDefaults(array(
            'interestId' => '0',
            'images' => ''
        ));
    }
}