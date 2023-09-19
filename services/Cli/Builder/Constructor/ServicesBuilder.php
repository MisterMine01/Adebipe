<?php

use Adebipe\Services\Interfaces\StarterServiceInterface;

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

    public static function decodedName($function_name): string
    {
        if (strpos($function_name, "\\Generated")) {
            $function_name = str_replace("\\Generated", "", $function_name);
        }
        return $function_name;
    }

    public static function getName($function_name): string
    {
        $function_name = self::decodedName($function_name);
        $function_name = "get" . implode('_', explode('\\', $function_name));
        return $function_name;
    }

    public function generate_function_constructor()
    {
        $class_name = $this->_class->getName();
        $class_name = self::decodedName($class_name);

        $function_name = self::getName($class_name);
        $function =  'function ' . $function_name . "() {\n";
        $function .= 'if (isset($_GLOBALS[\'' . $class_name . '\'])) {' . "\n";
        $function .= 'return $_GLOBALS[\'' . $class_name . '\'];' . "\n";
        $function .= '}' . "\n";
        $parameters = [];
        foreach ($this->_constructor_service_needed as $service) {
            $parameters[] = ServicesBuilder::getName($service) . '()';
        }
        $function .= '$_GLOBALS[\'' . $class_name . '\'] = new ' . $class_name . '(' . implode(",\n", $parameters) . ");\n";
        if (in_array(StarterServiceInterface::class, $this->_class->getInterfaceNames())) {
            $function_start = $this->_class->getMethod('atStart');
            $function_parameters = [];
            foreach ($function_start->getParameters() as $param) {
                $param_class = $param->getType()->getName();
                $function_parameters[] = ServicesBuilder::getName($param_class) . '()';
            }
            $function .= '$_GLOBALS[\'' . $class_name . '\']->atStart(' . implode(",\n", $function_parameters) . ");\n";
        }
        $function .= 'return $_GLOBALS[\'' . $class_name . '\'];';
        $function .= '}';
        return $function;
    }

    public function atEnd()
    {
        if (!in_array(StarterServiceInterface::class, $this->_class->getInterfaceNames())) {
            return '';
        }
        $class_name = $this->_class->getName();
        $class_name = self::decodedName($class_name);
        $function_end = $this->_class->getMethod('atEnd');
        $function_parameters = [];
        foreach ($function_end->getParameters() as $param) {
            $param_class = $param->getType()->getName();
            $function_parameters[] = ServicesBuilder::getName($param_class) . '()';
        }
        $function = 'if (isset($_GLOBALS[\'' . $class_name . '\'])) {' . "\n";
        $function = '$_GLOBALS[\'' . $class_name . '\']->atEnd(' . implode(",\n", $function_parameters) . ");\n";
        $function .= '}';
        return $function;
    }
}
