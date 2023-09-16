<?php


class ServicesBuilder
{
    private ReflectionClass $_class;
    private array $_constructor_service_needed = [];


    public function __construct(ReflectionClass $class)
    {
        $this->_class = $class;
        foreach ($this->_class->getConstructor()->getParameters() as $param) {
            $param_class = $param->getType()->getName();
            $this->_constructor_service_needed[] = $param_class;
        }
    }

}
