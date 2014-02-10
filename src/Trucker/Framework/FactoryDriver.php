<?php

namespace Trucker\Framework;

use Illuminate\Container\Container;

abstract class FactoryDriver
{

    /**
     * The IoC Container
     *
     * @var Illuminate\Container\Container
     */
    protected $app;


    /**
     * Build a new FactoryDriver
     *
     * @param Container $app
     */
    public function __construct(Container $app)
    {
        $this->app = $app;
    }


    /**
     * Getter to access the IoC Container
     * 
     * @return Container
     */
    public function getApp()
    {
        return $this->app;
    }


    /**
     * Setter for the IoC Container
     * 
     * @param Container
     * @return  void
     */
    public function setApp($app)
    {
        $this->app = $app;
    }


    /**
     * Get an option from the config file
     *
     * @param  string $option
     *
     * @return mixed
     */
    public function getOption($option)
    {
        return $this->app['config']->get('trucker::'.$option);
    }


    /**
     * Function to use other defined abstract methods to 
     * use a standard naming-convention based method of 
     * building classes by the factory
     * 
     * @return mixed - anything a subclass factory can build
     */
    public function build()
    {
        //get the prefix, suffix and namespace for the driver class
        $prefix = $this->getDriverNamePrefix();
        $suffix = $this->getDriverNameSuffix();
        $ns     = $this->getDriverNamespace();
        $driver = $this->getDriverConfigValue();

        //use naming convention to convert the driver name
        //into a fully quantified class name
        $klass = studly_case($driver);
        $fqcn  = "{$ns}\\{$prefix}{$klass}{$suffix}";

        try {
            $refl = new \ReflectionClass($fqcn);
        } catch (\ReflectionException $e) {
            throw new \InvalidArgumentException("Unsupported driver [{$driver}] to load [{$fqcn}]");
        }
        
        $instance = $refl->newInstanceArgs($this->getDriverArgumentsArray());

        return $instance;
    }


    /**
     * Function to return a string representaion of the namespace 
     * that all classes built by the factory should be contained within
     * 
     * @return string - namespace string
     */
    abstract public function getDriverNamespace();


    /**
     * Function to return a string that should be suffixed
     * to the studly-cased driver name of all the drivers
     * that the factory can return 
     * 
     * @return string
     */
    abstract public function getDriverNameSuffix();


    /**
     * Function to return a string that should be prefixed
     * to the studly-cased driver name of all the drivers
     * that the factory can return
     * 
     * @return string
     */
    abstract public function getDriverNamePrefix();

    /**
     * Function to return an array of arguments that should be
     * passed to the constructor of a new driver instance
     * 
     * @return array
     */
    abstract public function getDriverArgumentsArray();

    /**
     * Function to return the string representation of the driver 
     * itslef based on a value fetched from the config file.  This
     * function will itself access the config, and return the driver
     * setting
     * 
     * @return string
     */
    abstract public function getDriverConfigValue();
}
