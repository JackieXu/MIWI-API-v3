<?php


namespace AppBundle\Validator;


use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class InterestQueryValidator
 *
 * @package AppBundle\Validator
 */
class InterestQueryValidator extends QueryValidator
{
    /**
     * @param OptionsResolver $resolver
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefined(array(
            'defaultOnly'
        ));

        $resolver->setAllowedTypes('defaultOnly', 'bool');

        $resolver->setDefault('defaultOnly', false);
    }
}
