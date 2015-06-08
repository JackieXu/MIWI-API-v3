<?php


namespace AppBundle\Validator;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ShareObjectValidator
 *
 * @package AppBundle\Validator
 */
class ShareObjectValidator extends Validator
{
    /**
     * @param OptionsResolver $resolver
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined(array(
            'shareObject'
        ));

        $resolver->setRequired(array(
            'shareObject'
        ));

        $resolver->setAllowedTypes('shareObject', 'array');
    }
}