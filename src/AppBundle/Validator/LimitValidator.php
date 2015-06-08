<?php


namespace AppBundle\Validator;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class LimitValidator
 *
 * @package AppBundle\Validator
 */
class LimitValidator extends Validator
{
    /**
     * @param OptionsResolver $resolver
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefined(array(
            'limit',
            'offset'
        ));

        $resolver->setAllowedTypes('limit', 'numeric');
        $resolver->setAllowedTypes('offset', 'numeric');

        $resolver->setDefault('limit', 10);
        $resolver->setDefault('offset', 0);
    }
}
