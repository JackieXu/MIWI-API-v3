<?php


namespace AppBundle\Validator;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ProfileValidator
 *
 * @package AppBundle\Validator
 */
class ProfileValidator extends Validator
{
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefined(array(
            'extended'
        ));

        $resolver->setAllowedTypes('extended', 'string');
        $resolver->setDefault('extended', '0');
    }
}