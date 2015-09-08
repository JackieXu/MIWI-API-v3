<?php


namespace AppBundle\Validator;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class DeviceValidator
 *
 * @package AppBundle\Validator
 */
class DeviceValidator extends Validator
{
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined(array(
            'deviceType',
            'deviceId'
        ));

        $resolver->setRequired(array(
            'deviceType',
            'deviceId'
        ));

        $resolver->setAllowedValues('deviceType', array(
            'ios',
            'android'
        ));
    }
}