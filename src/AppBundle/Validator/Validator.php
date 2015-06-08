<?php


namespace AppBundle\Validator;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class Validator
 *
 * @package AppBundle\Validator
 */
class Validator
{
    /**
     * @var array
     */
    protected $options;

    /**
     * @param array $options
     */
    public function __construct(array $options)
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $this->options = $resolver->resolve($options);
    }

    /**
     * Get value from options
     *
     * @param string $key
     * @return mixed
     */
    public function getValue($key)
    {
        if (array_key_exists($key, $this->options)) {
            return $this->options[$key];
        }

        return null;
    }

    /**
     * @param OptionsResolver $resolver
     */
    protected function configureOptions(OptionsResolver $resolver)
    {

    }
}
