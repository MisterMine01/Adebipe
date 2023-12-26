<?php

namespace Adebipe\Cli\Builder;

use ReflectionClass;
use ReflectionNamedType;

/**
 * Build a service
 * This class is used to build a service
 *
 * @author BOUGET Alexandre <abouget68@gmail.com>
 */
class ServicesBuilder
{
    private string $_starter_service = "Adebipe\Services\Interfaces\StarterServiceInterface";
    private ReflectionClass $_class;
    private array $_constructor_service_needed = [];

    /**
     * Get the name of the service
     *
     * @param string $function_name Name of the function
     *
     * @return string Name of the service
     */
    public static function decodedName($function_name): string
    {
        if (strpos($function_name, "\\Generated")) {
            $function_name = str_replace("\\Generated", "", $function_name);
        }
        return $function_name;
    }

    /**
     * Get the name of the function service
     *
     * @param string $function_name Name of the function
     *
     * @return string Name of the function service
     */
    public static function getName($function_name): string
    {
        $function_name = self::decodedName($function_name);
        $function_name = "get" . implode('_', explode('\\', $function_name));
        return $function_name;
    }

    /**
     * Build a service
     *
     * @param ReflectionClass $class Class to build
     */
    public function __construct(ReflectionClass $class)
    {
        $this->_class = $class;
        if ($this->_class->getConstructor() === null) {
            return;
        }
        foreach ($this->_class->getConstructor()->getParameters() as $param) {
            $type = $param->getType();
            if ($type instanceof ReflectionNamedType) {
                $param_class = $type->getName();
                $this->_constructor_service_needed[] = $param_class;
                continue;
            }
            throw new \Exception('The service ' . $this->_class->getName() . ' has a parameter without type');
        }
    }

    /**
     * Generate the function to get the service
     *
     * @return string Function to get the service
     */
    public function generateFunctionConstructor()
    {
        $class_name = $this->_class->getName();
        $class_name = self::decodedName($class_name);

        $function_name = self::getName($class_name);
        $function =  'function ' . $function_name . "() {\n";
        $function .= 'if (isset($GLOBALS[\'' . $class_name . '\'])) {' . "\n";
        $function .= 'return $GLOBALS[\'' . $class_name . '\'];' . "\n";
        $function .= '}' . "\n";
        $parameters = [];
        foreach ($this->_constructor_service_needed as $service) {
            $parameters[] = ServicesBuilder::getName($service) . '()';
        }
        $function .= '$GLOBALS[\'' . $class_name . '\'] = ' .
            'new ' . $class_name . '(' . implode(",\n", $parameters) . ");\n";
        if (in_array($this->_starter_service, $this->_class->getInterfaceNames())) {
            $function_start = $this->_class->getMethod('atStart');
            $function_parameters = [];
            foreach ($function_start->getParameters() as $param) {
                $type = $param->getType();
                if ($type instanceof ReflectionNamedType) {
                    $param_class = $type->getName();
                    $function_parameters[] = ServicesBuilder::getName($param_class) . '()';
                    continue;
                }
                throw new \Exception('The service ' . $class_name . ' has a parameter without type');
            }
            $function .= '$GLOBALS[\'' . $class_name . '\']->atStart(' . implode(",\n", $function_parameters) . ");\n";
        }
        $function .= 'return $GLOBALS[\'' . $class_name . '\'];';
        $function .= '}';
        return $function;
    }

    /**
     * Generate the part of function to stop this service
     *
     * @return string Function to stop the service
     */
    public function atEnd()
    {
        if (!in_array($this->_starter_service, $this->_class->getInterfaceNames())) {
            return '';
        }
        $class_name = $this->_class->getName();
        $class_name = self::decodedName($class_name);
        $function_end = $this->_class->getMethod('atEnd');
        $function_parameters = [];
        foreach ($function_end->getParameters() as $param) {
            $type = $param->getType();
            if ($type instanceof ReflectionNamedType) {
                $param_class = $type->getName();
                $function_parameters[] = ServicesBuilder::getName($param_class) . '()';
                continue;
            }
            throw new \Exception('The service ' . $class_name . ' has a parameter without type');
        }
        $function = 'if (isset($GLOBALS[\'' . $class_name . '\'])) {' . "\n";
        $function .= '$GLOBALS[\'' . $class_name . '\']->atEnd(' . implode(",\n", $function_parameters) . ");\n";
        $function .= '}';
        return $function;
    }
}
