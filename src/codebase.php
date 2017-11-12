<?php declare(strict_types = 1);

// Grab these before we define our own classes
$internal_class_name_list = get_declared_classes();
$internal_interface_name_list = get_declared_interfaces();
$internal_trait_name_list = get_declared_traits();
// Get everything except user-defined constants
$internal_const_name_list = array_keys(array_merge(['true' => true, 'false' => false, 'null' => null], ...array_values(
    array_diff_key(get_defined_constants(true), ['user' => []])
)));

$internal_function_name_list = get_defined_functions()['internal'];

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
  // This is the normal path when Phan is installed only in the scope of a project.
  require_once __DIR__ . '/../vendor/autoload.php';
}
else {
  // This is the path to autoload.php when Phan is installed globally.
  require_once __DIR__ . '/../../../autoload.php';
}

use Phan\CodeBase;

return new CodeBase(
    $internal_class_name_list,
    $internal_interface_name_list,
    $internal_trait_name_list,
    $internal_const_name_list,
    $internal_function_name_list
);