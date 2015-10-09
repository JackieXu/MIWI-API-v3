<?php


namespace AppBundle\Validator;


use Symfony\Component\OptionsResolver\OptionsResolver;

class CommentValidator extends UserValidator
{
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined(array(
            'comment'
        ));
    }
}