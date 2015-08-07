<?php


namespace AppBundle\Validator;


use Symfony\Component\OptionsResolver\OptionsResolver;

class GroupValidator extends UserValidator
{
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined(array(
            'title',
            'description',
            'image',
            'interestId',
            'visibility'
        ));

        $resolver->setRequired(array(
            'title',
            'visibility',
            'interestId'
        ));

        $resolver->setAllowedValues('visibility', array('public', 'private'));

        $resolver->setDefault('description', '');
        $resolver->setDefault('image', '');
        $resolver->setDefault('visibility', 'public');

        $resolver->setAllowedTypes('interestId', 'numeric');
    }
}