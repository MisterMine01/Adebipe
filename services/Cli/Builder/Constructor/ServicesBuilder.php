<?php


class ServicesBuilder
{
    private ReflectionClass $_class;
    private array $_constructor_service_needed = [];


    public function __construct(ReflectionClass $class)
    {
        $this->_class = $class;
        if ($this->_class->getConstructor() === null) {
            return;
        }
        foreach ($this->_class->getConstructor()->getParameters() as $param) {
            $param_class = $param->getType()->getName();
            $this->_constructor_service_needed[] = $param_class;
        }
    }

    public static function getName($function_name): string
    {
        if (strpos($function_name, "\\Generated")) {
            $function_name = str_replace("\\Generated", "", $function_name);
        }
        $function_name = "get" . implode('_', explode('\\', $function_name));
        return $function_name;
    }

    public function generate_function_constructor()
    {
        $function_name = self::getName($this->_class->getName());
        $function =  'function ' . $function_name . "() {\n";
        $function .= 'if (isset($_GLOBALS[\'' . $this->_class->getName() . '\'])) {' . "\n";
        $function .= 'return $_GLOBALS[\'' . $this->_class->getName() . '\'];' . "\n";
        $function .= '}' . "\n";
        $parameters = [];
        foreach ($this->_constructor_service_needed as $service) {
            $parameters[] = 'get' . implode('_', explode('\\', $service)) . '()';
        }
        $function .= '$_GLOBALS[\'' . $this->_class->getName() . '\'] = new ' . $this->_class->getName() . '(' . implode(",\n", $parameters) . ");\n";
        $function .= 'return $_GLOBALS[\'' . $this->_class->getName() . '\'];';
        $function .= '}';
        return $function;
    }

}
