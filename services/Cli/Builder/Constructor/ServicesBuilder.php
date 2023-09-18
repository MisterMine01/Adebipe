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

    public function generate_function_constructor()
    {
        $function_name = $this->_class->getConstructor()->getName();
        $function_name = "get" . implode('_', explode('\\', $function_name));
        $function = 'function ' . $function_name . '()';
        $function .= '{';
        $function .= 'if isset($_GLOBALS[\'' . $this->_class->getName() . '\']) {';
        $function .= 'return $_GLOBALS[\'' . $this->_class->getName() . '\'];';
        $function .= '}';
        $parameters = [];
        foreach ($this->_constructor_service_needed as $service) {
            $parameters[] = 'get' . implode('_', explode('\\', $service)) . '()';
        }
        $function .= '$_GLOBALS[\'' . $this->_class->getName() . '\'] = new ' . $this->_class->getName() . '(' . implode(', ', $parameters) . ');';
        $function .= 'return $_GLOBALS[\'' . $this->_class->getName() . '\'];';
        $function .= '}';
    }

}
