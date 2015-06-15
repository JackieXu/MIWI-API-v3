<?php


namespace AppBundle\Validator;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TimelineValidator
 *
 * @package AppBundle\Validator
 */
class TimelineValidator extends LimitValidator
{
    /**
     * @param OptionsResolver $resolver
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefined(array(
            'userId'
        ));

        $resolver->setRequired(array(
            'userId'
        ));

        $resolver->setAllowedTypes('userId', 'numeric');
    }
}
