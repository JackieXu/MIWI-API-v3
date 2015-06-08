<?php


namespace AppBundle\Validator;


use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class QueryValidator
 *
 * @package AppBundle\Validator
 */
class QueryValidator extends LimitValidator
{
    /**
     * @param OptionsResolver $resolver
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefined(array(
            'query',
        ));

        $resolver->setAllowedTypes('query', 'string');

        $resolver->setDefault('query', '');
    }
}
