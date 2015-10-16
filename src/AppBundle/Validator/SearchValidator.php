<?php
/**
 * Created by PhpStorm.
 * User: JXu
 * Date: 15-10-2015
 * Time: 11:08
 */

namespace AppBundle\Validator;


use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchValidator extends QueryValidator
{
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefined(array(
            'interestId',
            'userId'
        ));

        $resolver->setAllowedTypes('userId', 'numeric');
        $resolver->setAllowedTypes('interestId', 'numeric');
        $resolver->setDefault('interestId', '0');
    }
}