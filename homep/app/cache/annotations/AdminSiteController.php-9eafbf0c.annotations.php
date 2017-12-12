<?php

return array(
  '#namespace' => 'controllers',
  '#uses' => array (
  'DAO' => 'micro\\orm\\DAO',
  'RequestUtils' => 'micro\\utils\\RequestUtils',
  'HtmlFormCheckbox' => 'Ajax\\semantic\\html\\collections\\form\\HtmlFormCheckbox',
  'models' => 'models',
  'Moteur' => 'models\\Moteur',
  'JsUtils' => 'Ajax\\JsUtils',
),
  '#traitMethodOverrides' => array (
  'controllers\\AdminSiteController' => 
  array (
  ),
),
  'controllers\\AdminSiteController' => array(
    array('#name' => 'property', '#type' => 'mindplay\\annotations\\standard\\PropertyAnnotation', 'type' => 'JsUtils', 'name' => 'jquery')
  ),
);

